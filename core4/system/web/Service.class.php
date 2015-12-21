<?php
/**
* Service.class.php
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
* A base class for services. This class defines some commonly used request handles.
* @package \System\Web
*/
abstract class Service extends \System\Base\StaticBase
{
    /**
    * @var \System\HTTP\Request\Get The GET request
    */
    protected static $get = null;
    /**
    * @var \System\HTTP\Request\Post The POST request
    */
    protected static $post = null;
    /**
    * @var \System\HTTP\Storage\Session The SESSION variable
    */
    protected static $session = null;
    /**
    * @var \System\HTTP\Storage\Cookie The COOKIE variable
    */
    protected static $cookie = null;

    /**
    * Validates the predefined handles. There is no need to call this function manually as it is done before calling the service
    * Successive calls have no effect.
    */
    public static final function validateHandles()
    {
        if (self::$get == null)
        {
            self::$get = new \System\HTTP\Request\Get();
        }
        if (self::$post == null)
        {
            self::$post = new \System\HTTP\Request\Post();
        }
        if (self::$session == null)
        {
            self::$session = new \System\HTTP\Storage\Session();
        }
        if (self::$cookie == null)
        {
            self::$cookie = new \System\HTTP\Storage\Cookie();
        }
    }
}