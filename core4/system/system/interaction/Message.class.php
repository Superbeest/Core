<?php
/**
* Message.class.php
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


namespace System\System\Interaction;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the message to be send
* @package \System\System\Interaction
*/
class Message extends \System\Base\Base
{
	/**
	* @publicget
	* @publicset
	* @var int The type of message
	*/
	protected $type = \System\System\Interaction\MessageType::TYPE_NONE;

	/**
	* @publicget
	* @publicset
	* @var string The message to be sent
	*/
	protected $message = '';

	/**
	* @publicget
	* @publicset
	* @var array The parameters to accompany the message
	*/
	protected $params = array();

	/**
	* Implements magic __sleep function to only export the given params
	* @return array The params to export
	*/
	public final function __sleep()
	{
		return array('type', 'message', 'params');
	}
}