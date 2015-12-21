<?php
/**
* OnInteractionEvent.event.php
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


namespace System\System\Interaction\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The event to process the Interaction
* @package \System\System\Interaction\Event
*/
class OnInteractionEvent extends \System\Event\EventHandler
{
	/**
	* @publicget
	* @publicset
	* @var \System\Db\Database The database to work with
	*/
	protected $database;

	/**
	* @publicset
	* @publicget
	* @var \System\System\Interaction\Message The message to work with. This can be any type
	*/
	protected $message;

	/**
	* @publicget
	* @var \System\Collection\Vector The responses to send back to the server
	*/
	protected $responses = null;

	/**
	* Constructs the object
	*/
	public final function __construct()
	{
		$this->responses = new \System\Collection\Vector();
	}

	/**
	* Adds a response to the stack
	* @param \System\System\Interaction\Response The response to add
	*/
	public final function addResponse(\System\System\Interaction\Response $response)
	{
		$this->responses[] = $response;
	}
}