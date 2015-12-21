<?php
/**
* Connection.class.php
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


namespace System\Web\Websocket;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements a single connection container
* @package \System\Web\Websocket
*/
final class Connection extends \System\Base\BaseObj
{
	/**
	* @publicget
	* @publicset
	* @var resource The socket corresponding to the connection
	*/
	protected $socket;

	/**
	* @publicget
	* @publicset
	* @var string The unique identifier for the current connection
	*/
	protected $id;

	/**
	* @publicget
	* @publicset
	* @var array The headers in a request
	*/
	protected $headers = array();

	/**
	* @publicget
	* @publicset
	* @var string The handshake part
	*/
	protected $handshake = '';

	/**
	* @publicget
	* @publicset
	* @var bool True if we are handling partial packets
	*/
	protected $handlingPartialPacket = false;

	/**
	* @publicget
	* @publicset
	* @var string The partial buffer
	*/
	protected $partialBuffer = "";

	/**
	* @publicget
	* @publicset
	* @var bool True if we are continuously sending packets
	*/
	protected $sendingContinuous = false;

	/**
	* @publicget
	* @publicset
	* @var string The partial message
	*/
	protected $partialMessage = "";

	/**
	* @publicset
	* @publicget
	* @var string The requested resource
	*/
	protected $requestedResource = "";

	/**
	* @publicget
	* @publicset
	* @var bool True if a close message has been sent
	*/
	protected $sentClose = false;

	/**
	* Creates an instance of the connection based on the socket
	* @param resource The socket to create a connection object for
	*/
	public function __construct($socket)
	{
		$this->setId(uniqid());
		$this->setSocket($socket);
	}
}