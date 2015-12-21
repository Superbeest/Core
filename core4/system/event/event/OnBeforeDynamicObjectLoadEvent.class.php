<?php
/**
* OnBeforeDynamicObjectLoadEvent.event.php
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


namespace System\Event\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Event called when a DynamicBaseObject is about to be loaded and we want to influence the loading
* @package \System\Event\Event
*/
class OnBeforeDynamicObjectLoadEvent extends \System\Event\EventHandler
{
	/**
	* @publicget
	* @publicset
	* @var \System\Db\Database The database object to query
	*/
	protected $database;

	/**
	* @publicget
	* @publicset
	* @var string The full classname of the class to use as the base object.
	*/
	protected $dynamicClassName;

	/**
	* @publicget
	* @publicset
	* @var string The condition string as described in the objects xml file
	*/
	protected $condition;

	/**
	* @publicget
	* @publicset
	* @validatehandle
	* @var \System\Collection\Vector The parameters to be used in the condition. If an item in the vector is a vector itself, its items are imploded by ', ' and treated as a \System\Db\QueryType::TYPE_QUERY parameter
	*/
	protected $parameters;

	/**
	* @publicget
	* @publicset
	* @var bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.
	*/
	protected $alwaysUseContainer;

	/**
	* @publicget
	* @publicset
	* @var bool When true, the secondary db connection pipe is used to read the data from.
	*/
	protected $useSecondaryDatabasePipe;
}