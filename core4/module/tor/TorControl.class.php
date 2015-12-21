<?php
/**
* TorControl.class.php
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


namespace Module\Tor;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Implements methods to control the Tor Circuit using the control port
* @package \Module\Tor
*/
final class TorControl extends \System\Base\BaseObj
{
	/**
	* The default control port to connect to
	*/
	const DEFAULT_CONTROL_PORT = 9051;

	/**
	* The default connection timeout
	*/
	const DEFAULT_TIMEOUT = 5;

	/**
	* The default in seconds for a refresh.
	*/
	const DEFAULT_REFRESH_DELAY = 10;

	/**
	* @var resource Holds the socket for the torrc connection
	*/
	private $socket = null;

	/**
	* Creates the TorControl object to issue commands through.
	* @param string The host. This may be over a transport layer (http), or an ip
	* @param string The password to connect with the controlport.
	* @param int The portnumber to connect over
	* @param int The amount of time to wait in seconds for connection attempts
	*/
	public final function __construct($host, $password, $port = self::DEFAULT_CONTROL_PORT, $timeout = self::DEFAULT_TIMEOUT)
	{
		$errno = '';
		$errstr = '';

		$handle = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$handle)
		{
			throw new \System\Error\Exception\InvalidHTTPCallException('Could not connect to tor control port on ' . $host . ':' . $port . '. Errno: ' . $errno . '. Errstr: ' . $errstr);
		}

		fputs($handle, 'AUTHENTICATE "' . $password . '"' . "\r\n");
		$response = fread($handle, 1024);
		list($code, $text) = explode(' ', $response, 2);
        if ($code != '250')
        {
        	throw new \System\Error\Exception\InvalidHTTPCallException('Authentication failed at ' . $host . ':' . $port . '. Response: ' . $response);
		}

		$this->socket = $handle;
	}

	/**
	* Closes the socket connection and cleans up the object
	*/
	public final function __destruct()
	{
		if (is_resource($this->socket))
		{
			fclose($this->socket);
		}
	}

	/**
	* Request a new TOR circuit identity.
	* @param int The amount of seconds to wait for a refresh tor circuit
	*/
	public final function newIdentity($delayInSec = self::DEFAULT_REFRESH_DELAY)
	{
		fputs($this->socket, 'signal NEWNYM' . "\r\n");
		$response = fread($this->socket, 1024);
		list($code, $text) = explode(' ', $response, 2);
        if ($code != '250')
        {
        	throw new \System\Error\Exception\InvalidHTTPCallException('Request for circuit renewal failed with: ' . $response);
		}

		//delay to make sure we get a new circuit
		sleep($delayInSec);
	}
}