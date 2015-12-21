<?php
/**
* SMTP.class.php
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
* Facilitates the communication with a SMTP server
* @package \System\Email
*/
final class SMTP extends \System\Base\Base
{
    /**
    * The default port to use
    */
    const DEFAULT_PORT = 25;

    /**
    * The default timeout for the SMTP in seconds
    */
    const DEFAULT_TIMEOUT = 10;

    /**
    * @var resource The handle to the socket
    */
    private $handle = null;

    /**
    * @publicget
    * @var string The Log file with the communication
    */
    protected $log = '';

    /**
    * Creates a connection to the given SMTP server using the default connection settings.
    * @param string The ip, or URL to the SMTP server
    */
    public final function __construct_1($host)
    {
        $this->__construct_3($host, self::DEFAULT_PORT, self::DEFAULT_TIMEOUT);
    }

    /**
    * Creates a connection to the given SMTP server using the given connection settings.
    * @param string The ip, or URL to the SMTP server
    * @param int The port to use
    * @param int The timeout in seconds
    */
    public final function __construct_3($host, $port, $timeout)
    {
        $errno = 0;
        $errstr = '';

        $socket = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$socket)
        {
            throw new \System\Error\Exception\SystemException('Could not connect to the SMTP server');
        }

        $this->handle = $socket;
        $this->getLine();
    }

    /**
    * Destroys the object and closes the socket connection
    */
    public final function __destruct()
    {
        $this->close();
    }

    /**
    * Closes the connection with the SMTP server
    */
    public final function close()
    {
        if ($this->handle)
        {
            $this->sendCommand('QUIT');
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
    * Sends a raw SMTP command to the SMTP server
    * Endlines are automatically added.
    * @param string The command to send
    */
    public final function sendCommand($command)
    {
        $this->log .= nl2br(htmlentities("C $command\r\n"));

        if (!fputs($this->handle, $command . "\r\n"))
        {
            throw new \System\Error\Exception\SystemException("Error in sending command: " . $this->log);
        }

        $this->getLine();
    }

    /**
    * Gets a return command from the server
    * @return string The result
    */
    private final function getLine()
    {
        $result = "";
        do
        {
            $line = fgets($this->handle, 2048);

            $this->log .= nl2br(htmlentities("S " . $line));

            $a = explode(" ", $line);
            if ($a[0] == "250-AUTH")
            {
                $result .= substr($line, 9);
            }
        } while (!is_numeric($a[0]));

        if ($a[0] == 250)
        {
            return $result;
        }
        else
        {
            return $a[0];
        }
    }
}