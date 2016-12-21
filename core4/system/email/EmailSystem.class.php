<?php
/**
* EmailSystem.class.php
*
* Copyright c 2015, SUPERHOLDER. All rights reserved.
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or at your option any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301  USA
*/


namespace System\Email;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Interface that allowes the sending of emails using direct mail or queued mail
* @package \System\Email
*/
final class EmailSystem extends \System\Base\StaticBase
{
    /**
    * @publicget
    * @var string The log of the EmailSystem dispatcher.
    */
    protected static $log = '';

    /**
    * Low priority
    */
    const PRIORITY_LOW = 0;
    /**
    * Normal priority
    */
    const PRIORITY_NORMAL = 1;
    /**
    * High priority
    */
    const PRIORITY_HIGH = 2;

    /**
    * @var array Contains the names of the priority levels
    */
    private static $priorityNames = array(
        self::PRIORITY_LOW => array("PRIORITY_LOW", 'queue_low'),
        self::PRIORITY_NORMAL => array("PRIORITY_NORMAL", 'queue_normal'),
        self::PRIORITY_HIGH => array("PRIORITY_HIGH", 'queue_high'));

    /**
    * Puts an email message to the queue for sending.
    * @param EmailMessage The email to send
    * @param int The priority of the message
    */
    public final static function queueMail(\System\Email\EmailMessage $message, $priority = \System\Email\EmailSystem::PRIORITY_NORMAL)
    {
        $database = self::getMailQueue();

        self::addMailToQueue($database, $message, $priority);

        if ($message->getAttachments()->hasItems())
        {
            self::processAttachments($database, $message, $priority);
        }
    }

    /**
    * Gets the MailQueue database
    * @return \System\Db\Database The mail queue database
    */
    private final static function getMailQueue()
    {
		//we define the emailsystem port so we can override it manually if needed
    	defined('EMAILSYSTEM_PORT') || define ('EMAILSYSTEM_PORT', DATABASE_PORT);
    	defined('EMAILSYSTEM_PERSISTANT') || define ('EMAILSYSTEM_PERSISTANT', DATABASE_PERSISTANT);

        if ((!defined('EMAILSYSTEM_HOST')) ||
            (!defined('EMAILSYSTEM_USER')) ||
            (!defined('EMAILSYSTEM_PASSWORD')) ||
            (!defined('EMAILSYSTEM_NAME')))
        {
            throw new \System\Error\Exception\SystemException('Please set the EMAILSYSTEM_HOST, EMAILSYSTEM_USER, EMAILSYSTEM_PASSWORD and EMAILSYSTEM_NAME, EMAILSYSTEM_PORT, EMAILSYSTEM_PERSISTANT config directives');
        }

        return \System\Db\Database::getConnection(EMAILSYSTEM_HOST, EMAILSYSTEM_USER, EMAILSYSTEM_PASSWORD, EMAILSYSTEM_NAME, EMAILSYSTEM_PORT, EMAILSYSTEM_PERSISTANT);
    }

    /**
    * Adds the email message to the queue by using the passed query
    * @param \System\Db\Database The database to use
    * @param EmailMessage The message to send
    * @param int The priority to use
    */
    private final static function addMailToQueue(\System\Db\Database $database, \System\Email\EmailMessage $message, $priority)
    {
        $query = new \System\Db\Query($database, \System\Email\SQL_EMAILSYSTEM_ADDMAIL);
        $query->bind(self::$priorityNames[$priority][1], \System\Db\QueryType::TYPE_QUERY);
        $query->bind($message->getFrom(), \System\Db\QueryType::TYPE_STRING);
        $query->bind($message->getTo(), \System\Db\QueryType::TYPE_STRING);
        $query->bind($message->getCc(), \System\Db\QueryType::TYPE_STRING);
        $query->bind($message->getBcc(), \System\Db\QueryType::TYPE_STRING);
        $query->bind($message->getSubject(), \System\Db\QueryType::TYPE_STRING);
        $query->bind($message->getMessage(), \System\Db\QueryType::TYPE_STRING);

        $database->query($query);
    }

    /**
    * Immediatly sends the mail through the SMTP server
    * @param EmailMessage $message
    */
    public final static function sendMail(\System\Email\EmailMessage $message)
    {
        $smtp = self::getSMTPConnection();

        $attachments = new \System\Collection\SecureVector();
        foreach ($message->getAttachments() as $attachment)
        {
            if (!$attachment instanceof \System\IO\File)
            {
                throw new \InvalidArgumentException('Given attachment is not of type \System\IO\File');
            }

            $std = new \stdClass();
            $std->attach_mime = $attachment->getMimeType();
            $std->attach_filename = $attachment->getFilename();
            $std->attach_blob = base64_encode($attachment->getContents());

            $attachments[] = $std;
        }

        self::dispatchSingleMail(
            $smtp,
            $message->getFrom(),
            array($message->getTo()),
            array($message->getCc()),
            array($message->getBcc()),
            $message->getSubject(),
            $message->getMessage(),
            $attachments);

        $smtp->close();
    }

    /**
    * Attaches the emailattachments to the system in the queue
    *
    * @param \System\Db\Database The database to use
    * @param EmailMessage The email
    * @param int The priority of the mail
    */
    private final static function processAttachments(\System\Db\Database $database, \System\Email\EmailMessage $email, $priority)
    {
        $attachments = $email->getAttachments();

        $mailId = $database->getInsertId();

        foreach ($attachments as $attachment)
        {
            if ($attachment->exists())
            {
                $data = base64_encode($attachment->getContents());

                $filename = $attachment->getFilename();
                $mimetype = $attachment->getMimeType();

                $query = new \System\Db\Query($database, \System\Email\SQL_EMAILSYSTEM_ADD_ATTACH);
                $query->bind($data, \System\Db\QueryType::TYPE_STRING);
                $query->bind($filename, \System\Db\QueryType::TYPE_STRING);
                $query->bind($mimetype, \System\Db\QueryType::TYPE_STRING);
                $query->bind($mailId, \System\Db\QueryType::TYPE_INTEGER);
                $query->bind($priority, \System\Db\QueryType::TYPE_INTEGER);

                $database->query($query);
            }
        }
    }

    /**
    * This function dispatches mails from the mailqueue database to the SMTP server.
    * There are 3 levels of mail priority. Mails with the highest priority always get send.
    * Mails with normal priority get send with a limited batch.
    * Mails with low priority get send only if there are no normal or high priority mails with a limited batch.
    */
    public final static function dispatchMail()
    {
        $db = self::getMailQueue();

        $smtp = self::getSMTPConnection();

        $query = new \System\Db\Query($db, \System\Email\SQL_EMAILSYSTEM_GET_HIGH);
        $highPriorityMails = $db->query($query);
        self::iterateMailResults($db, $smtp, $highPriorityMails, self::PRIORITY_HIGH);

        $query = new \System\Db\Query($db, \System\Email\SQL_EMAILSYSTEM_GET_NORMAL);
        $query->bind(EMAILSYSTEM_SMTP_HANDLEMAILS, \System\Db\QueryType::TYPE_INTEGER);
        $normalPriorityMails = $db->query($query);
        self::iterateMailResults($db, $smtp, $normalPriorityMails, self::PRIORITY_NORMAL);

        if (($highPriorityMails->count() == 0) &&
            ($normalPriorityMails->count() == 0))
        {
            $query = new \System\Db\Query($db, \System\Email\SQL_EMAILSYSTEM_GET_LOW);
            $query->bind(EMAILSYSTEM_SMTP_HANDLEMAILS, \System\Db\QueryType::TYPE_INTEGER);
            $lowPriorityMails = $db->query($query);
            self::iterateMailResults($db, $smtp, $lowPriorityMails, self::PRIORITY_LOW);
        }

        $smtp->close();
    }

    /**
    * Iterates through the given resultset, dispatching every single mail with the given priority.
    * @param \System\Db\Database The database to use for the emailsystem queue
    * @param SMTP The smtp to use
    * @param \System\Db\DatabaseResult The results to iterate
    * @param int The priority level
    */
    private final static function iterateMailResults(\System\Db\Database $db, \System\Email\SMTP $smtp, \System\Db\DatabaseResult $results, $priority)
    {
        foreach ($results as $result)
        {
            $attachQuery = new \System\Db\Query($db, \System\Email\SQL_EMAILSYSTEM_GET_ATTACH);
            $attachQuery->bind($result->id, \System\Db\QueryType::TYPE_INTEGER);
            $attachQuery->bind($priority, \System\Db\QueryType::TYPE_INTEGER);
            $attachments = $db->query($attachQuery);

            self::addToLog('Processing ' . $results->count() . ' ' . self::$priorityNames[$priority][0] . ' priority mails');

            self::dispatchSingleMail(
                $smtp,
                $result->from,
                self::convertAddressStringToArray($result->to),
                self::convertAddressStringToArray($result->cc),
                self::convertAddressStringToArray($result->bcc),
                $result->subject,
                $result->message,
                $attachments);

            $query = new \System\Db\Query($db, \System\Email\SQL_EMAILSSYTEM_DELETEMAIL);
            $query->bind(self::$priorityNames[$priority][1], \System\Db\QueryType::TYPE_QUERY);
            $query->bind($result->id, \System\Db\QueryType::TYPE_INTEGER);
            $db->query($query);

            $query = new \System\Db\Query($db, \System\Email\SQL_EMAILSYSTEM_DELETEATTACH);
            $query->bind($result->id, \System\Db\QueryType::TYPE_INTEGER);
            $query->bind($priority, \System\Db\QueryType::TYPE_INTEGER);
            $db->query($query);
        }
    }

    /**
    * Adds the given line to the log variable. Auto breaklines.
    * @param string The line to add.
    */
    private final static function addToLog($log)
    {
        self::$log .= $log . "\r\n";
    }

    /**
    * Converts an address string to an array for use
    * @param string The address string
    * @return array An array with the addresses
    */
    private final static function convertAddressStringToArray($string)
    {
        return preg_split('/[,;]/sim', $string);
    }

    /**
    * Dispatches a single mail through the given SMTP connection. The mail gets dispatched immediately in both plaintext as html variant.
    * @param SMTP The SMTP connection to use
    * @param string The from address
    * @param array The recipients
    * @param array The CC recipients
    * @param array The BCC recipients
    * @param string The subject for the mail
    * @param string The (html) mail to use. Plain text variant gets generated automatically.
    * @param \System\Collection\Vector The attachments to send.
    */
    private final static function dispatchSingleMail(\System\Email\SMTP $smtp, $from, array $to, array $cc = array(), array $bcc = array(), $subject, $message, \System\Collection\SecureVector $attachments)
    {
        $smtp->sendCommand('MAIL FROM: <' . $from . '>');

        //we create a list of all recipients
        $recipients = array_filter(array_merge($to, $cc, $bcc));
        foreach ($recipients as $recipient)
        {
            if (!empty($recipient))
            {
                //strip out unwanted characters and only leave the emailaddress if we use the abc <abc@abc.com> format.
                $recipient = trim(preg_replace("/([\w\s]+)<([\S@._-]*)>/", " $2", $recipient));
                $smtp->sendCommand('RCPT TO: <' . $recipient . '>');
            }
        }

        self::addToLog('Processing mail: ' . $subject . ' to ' . implode(', ', $recipients));

        $smtp->sendCommand('DATA');

        $toHeader = trim(preg_replace("/([\w\s]+)<([\S@._-]*)>/", " $2", implode(", ", $to)));
        $ccHeader = trim(preg_replace("/([\w\s]+)<([\S@._-]*)>/", " $2", implode(", ", $cc)));

        $headers = "To: $toHeader\r\n";
        $headers .= "From: $from\r\n";
        if (!empty($ccHeader))
        {
            $headers .= "CC: $ccHeader\r\n";
        }
        $headers .= "Subject: $subject\r\n";

		$headers .= "Date: " . date('r', time()) . "\r\n";

        $headers .= "Reply-To: $from\r\n";
        $headers .= "Return-Path: $from\r\n";
        $headers .= "X-Mailer: SuperHolderMailer\r\n";

        //we only support MIME v1 with multipart text
        $headers .= "MIME-Version: 1.0\r\n";
        $boundaryMain = uniqid('', true);
        $headers .= "Content-Type: multipart/mixed; boundary=" . $boundaryMain . "\r\n";

        $headers .= "\r\n--" . $boundaryMain . "\r\n";
        $boundaryAlternative = uniqid('', true);
        $headers .= "Content-Type: multipart/alternative; boundary=" . $boundaryAlternative . "\r\n\r\n";

        //plain text variant
        $headers .= "--" . $boundaryAlternative . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "\r\n";
        $headers .= strip_tags($message);
        $headers .= "\r\n\r\n";

        //html variant
        $headers .= "--" . $boundaryAlternative . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "\r\n";
        $headers .= $message;
        $headers .= "\r\n\r\n";

        //close open boundary
        $headers .= "--" . $boundaryAlternative . "--\r\n";

        //add all the attachments
        foreach ($attachments as $attachment)
        {
            $headers .= "--" . $boundaryMain . "\r\n";
            $headers .= "Content-Type: " . $attachment->attach_mime . "; name=\"" . $attachment->attach_filename . "\"\r\n";
            $headers .= "Content-Disposition: attachment; filename=\"" . $attachment->attach_filename . "\"\r\n";
            $headers .= "Content-Transfer-Encoding: base64\r\n";
            $headers .= "\r\n";
            $headers .= chunk_split($attachment->attach_blob);
            $headers .= "\r\n";
        }

        $headers .= "--" . $boundaryMain . "--\r\n";
        $smtp->sendCommand("$headers\r\n."); //we send an extra line with an empty . for end of message, according to protocol
    }

    /**
    * Establishes a SMTP connection
    * @return \System\Email\SMTP The SMTP connection
    */
    private final static function getSMTPConnection()
    {
        if ((!defined('EMAILSYSTEM_SMTP_HOST')) ||
            (!defined('EMAILSYSTEM_SMTP_PORT')) ||
            (!defined('EMAILSYSTEM_SMTP_TIMEOUT')) ||
            (!defined('EMAILSYSTEM_SMTP_PASSWORD')) ||
            (!defined('EMAILSYSTEM_SMTP_HANDLEMAILS')) ||
            (!defined('EMAILSYSTEM_SMTP_USERNAME')))
        {
            throw new \System\Error\Exception\SystemException('Please set the EMAILSYSTEM_SMTP_HOST, EMAILSYSTEM_SMTP_PORT, EMAILSYSTEM_SMTP_TIMEOUT, EMAILSYSTEM_SMTP_PASSWORD, EMAILSYSTEM_SMTP_HANDLEMAILS and EMAILSYSTEM_SMTP_USERNAME config directives');
        }

        $server = new \System\HTTP\Request\Server();
        $smtp = new \System\Email\SMTP(EMAILSYSTEM_SMTP_HOST, EMAILSYSTEM_SMTP_PORT, EMAILSYSTEM_SMTP_TIMEOUT);

        $smtp->sendCommand('EHLO ' . $server->get('SERVER_ADDR'));

        $smtp->sendCommand('AUTH LOGIN');
        $smtp->sendCommand(base64_encode(EMAILSYSTEM_SMTP_USERNAME));
        $smtp->sendCommand(base64_encode(EMAILSYSTEM_SMTP_PASSWORD));

        return $smtp;
    }
}
