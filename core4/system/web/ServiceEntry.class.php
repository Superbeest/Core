<?php
/**
* ServiceEntry.class.php
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


namespace System\Web;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the ServiceEntry
* @package \System\Web
*/
final class ServiceEntry extends \System\Base\Base
{
    const ACTIONTYPE_POST = 1;
    const ACTIONTYPE_GET = 2;
    const ACTIONTYPE_SESSION = 3;
    const ACTIONTYPE_COOKIE = 4;

    const ACTION_FIELD = 'action';

    /**
    * The callback to call.
    * @publicget
    * @var string The callback to call upon fire.
    */
    protected $callback = '';
    /**
    * The action on which to fire. If empty, then we fire on every call.
    * @publicget
    * @var string The actionname
    */
    protected $actionName = '';

    /**
    * Defines where to look for the action variable.
    * Defaults to the POST.
    * @publicget
    * @var int The location to look for the action variable.
    */
    protected $actionType = self::ACTIONTYPE_POST;

    /**
    * @publicget
    * @publicset
    * @var string The name of the field to look for in the specified ActionType
    */
    protected $actionField = self::ACTION_FIELD;

    /**
    * Constructor for the ServiceEntry. The ServiceEntry will only be fired if the POST request contains a matching 'ACTION_FIELD' field.
    * @param callback Defines a callback to call for the service
    */
    public final function __construct_1($callback)
    {
        $this->callback = $callback;
        $this->actionName = '';
    }

    /**
    * Constructor for the ServiceEntry. The ServiceEntry will only be fired if the POST request contains a matching 'ACTION_FIELD' field.
    * If the given $actionName is '', then it fires on all requests.
    * @param callback Defines a callback to call for the service
    * @param string The action name. This may be empty for fire on all actions.
    */
    public final function __construct_2($callback, $actionName)
    {
        $this->callback = $callback;
        $this->actionName = $actionName;
    }

    /**
    * Constructor for the ServiceEntry. The ServiceEntry will only be fired if the $actionType request contains a matching 'ACTION_FIELD' field.
    * If the given $actionName is '', then it fires on all requests.
    * @param callback Defines a callback to call for the service
    * @param string The action name. This may be empty for fire on all actions.
    * @param int The action type. Define this to set the position to look
    */
    public final function __construct_3($callback, $actionName, $actionType)
    {
        $this->__construct_2($callback, $actionName);
        $this->actionType = $actionType;
    }
}
