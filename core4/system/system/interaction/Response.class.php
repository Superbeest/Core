<?php
/**
* Response.class.php
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
* The reply to the message
* @package \System\System\Interaction
*/
class Response extends \System\Base\Base
{
	/**
	* @publicget
	* @var \System\System\Interaction\Message The message to respond to
	*/
	protected $originalMessage = null;

	/**
	* @publicset
	* @publicget
	* @var mixed The respons
	*/
	protected $value;

	/**
	* Constructs the object
	* @param \System\System\Interaction\Message The original message
	* @param mixed The value to reply with
	*/
	public function __construct(\System\System\Interaction\Message $originalMessage, $value)
	{
		$this->originalMessage = $originalMessage;
		$this->value = $value;
	}

	/**
	* Implements magic __sleep function to only export the given params
	* @return array The params to export
	*/
	public final function __sleep()
	{
		return array('originalMessage', 'value');
	}
}