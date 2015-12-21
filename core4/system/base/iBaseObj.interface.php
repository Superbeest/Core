<?php
/**
* iBaseObj.interface.php
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
* All classes that behave like a base class should implement this interface
* @package \System\Base
*/
interface iBaseObj
{
    /**
    * Enforce the magic PHP function toString. It enforces a string representation of the object
    * @return string A string representation of the object
    */
    public function __toString();

    /**
    * Enforce the magic PHP function toString. It enforces a string representation of the object
    * @return string A string representation of the object
    */
    public function toString();

    /**
	* Returns the current classname, including namespaces
	* @return string The name of the class
	*/
    public function getClassName();

    /**
	* Returns the current classname, excluding namespaces
	* @return string The name of the class
	*/
    public function getBaseClassName();

    /**
	* Returns the name of the current static class. This resolves using late static binding
	* and thus returns the name from the class it was called on.
	* @return string The name of the class
	*/
    public static function getStaticClassName();

    /**
	* Returns the name of the current static class. This resolves using late static binding, excluding namespaces
	* and thus returns the name from the class it was called on.
	* @return string The name of the class
	*/
    public static function getStaticBaseClassName();
}