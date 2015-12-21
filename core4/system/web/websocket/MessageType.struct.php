<?php
/**
* MessageType.struct.php
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
* Defines the message types
* @package \System\Web\Websocket
*/
class MessageType extends \System\Base\BaseStruct
{
	const TYPE_CONTINUOUS = 0;
	const TYPE_TEXT = 1; //but should be 0 if sendingcontinuous
	const TYPE_BINARY = 2; //but should be 0 if sendingcontinuous
	const TYPE_CLOSE = 8;
	const TYPE_PING = 9;
	const TYPE_PONG = 10;

	/**
	* Gets the correct messagetype value, based on the current connection object
	* @param \System\Web\Websocket\Connection The connection object
	* @param int The requested messagetype
	* @return int The messagetype value
	*/
	public static function getMessageType(\System\Web\Websocket\Connection $connection, $messageType)
	{
		switch ($messageType)
		{
			case self::TYPE_TEXT:
				return $connection->isSendingContinuous() ? 0 : self::TYPE_TEXT;
			case self::TYPE_BINARY:
				return $connection->isSendingContinuous() ? 0 : self::TYPE_BINARY;
			default:
				return $messageType;
		}
	}
}