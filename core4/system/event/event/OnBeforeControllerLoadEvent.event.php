<?php
/**
* OnBeforeControllerLoadEvent.class.php
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
* Event called when before the requested controller will be loaded.
* @package \System\Event\Event
*/
class OnBeforeControllerLoadEvent extends \System\Event\EventHandler
{
	/**
    * @publicget
    * @publicset
    * @var string The Controllerrequest.
    */
    protected $controllerRequest = null;

    /**
    * @publicset
    * @publicget
    * @var string A replacement controller to be executed instead.
    */
    protected $controllerReplacement = null;

    /**
    * @publicget
    * @publicset
    * @var \System\Web\Controller The controller that is about to be executed
    */
    protected $controller = null;

    /**
    * @publicget
    * @publicset
    * @var string The name of the requested module. Do note this module name is not validated, it is just requested and may not exist.
    */
    protected $moduleName = null;

	/**
	* @publicget
	* @publicset
	* @var string The name of the requested action. Do note this function name is not validated, it is just requested and may not exist.
	*/
    protected $actionName = null;

	/**
	* @publicget
	* @publicset
	* @var string The method used to make the request
	*/
    protected $method = \System\HTTP\Request\Method::METHOD_GET;
}
