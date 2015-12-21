<?php
/**
* EmailMessage.class.php
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
* A representation of an Email message
* @package \System\Email
*/
class EmailMessage extends \System\Base\Base
{
    /**
    * @publicget
    * @publicset
    * @var string From string. This should be an emailaddress, or chained addresses (comma separated).
    */
    protected $from = '';

    /**
    * @publicget
    * @publicset
    * @var string To string. This should be an emailaddress, or chained addresses (comma separated).
    */
    protected $to = '';

    /**
    * @publicget
    * @publicset
    * @var string CC string. This should be an emailaddress, or chained addresses (comma separated).
    */
    protected $cc = '';

    /**
    * @publicget
    * @publicset
    * @var string BCC string. This should be an emailaddress, or chained addresses (comma separated).
    */
    protected $bcc = '';

    /**
    * @publicget
    * @publicset
    * @var string The subject of the email
    */
    protected $subject = '';

    /**
    * @publicget
    * @publicset
    * @var string The actual message to be send. This may be in HTML form, or plain text, but the mail is handled as HTML.
    */
    protected $message = '';

    /**
    * @publicget
    * @var \System\Collection\Vector The vector with full \System\IO\File objects of the attachments
    */
    protected $attachments = null;

	/**
	* Creates a new email message and does not populate it. It does empty the attachments variabel
	*/
	public final function __construct_0()
	{
		$this->attachments = new \System\Collection\Vector();
	}

    /**
    * Creates a new email message and populates it.
    * @param string From string. This should be an emailaddress
    * @param string To string. This should be an emailaddress.
    * @param string The subject of the email
    * @param string The actual message to be send. This may be in HTML form, or plain text, but the mail is handled as HTML.
    */
    public final function __construct_4($from, $to, $subject, $message)
    {
        $this->attachments = new \System\Collection\Vector();

        $this->setFrom($from);
        $this->setTo($to);
        $this->setSubject($subject);
        $this->setMessage($message);
    }

    /**
    * Adds an attachment to the Email message.
    * @param \System\IO\File The file object to attach
    */
    public final function addAttachment(\System\IO\File $file)
    {
        if ($file->exists())
        {
            $this->attachments[] = $file;
        }
        else
        {
            throw new \System\Error\Exception\FileNotFoundException('The given email attachment file ' . $file->getFilename() . ' does not exists');
        }
    }
}