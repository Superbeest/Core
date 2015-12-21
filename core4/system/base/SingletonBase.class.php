<?php
/**
* SingletonBase.class.php
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


namespace System\Base;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This class functions as the base singletonclass for most singleton classes
* @package \System\Base
*/
class SingletonBase extends \System\Base\BaseObj
{
    /**
    * The instance variable. It contains all the singleton items
    */
    private static $instances = null;

    /**
    * Retrieves the single instance of the current class.
    * @return mixed The only existing instance of the current class.
    */
    public static final function getInstance()
    {
        if (self::$instances == null)
        {
            self::$instances = new \System\Collection\Map();
        }

        $currentClass = get_called_class();
        if (!isset(self::$instances->$currentClass))
        {
            self::$instances->$currentClass = new $currentClass();
        }

        return self::$instances->$currentClass;
    }

    /**
    * Protected constructor to enforce the singleton pattern
    */
    protected function __construct()
    {
    }

    /**
    * Enforce the restriction of the singleton. Class may not be cloned
    */
    public final function __clone()
    {
        throw new \Exception('This class may not be cloned');
    }
}