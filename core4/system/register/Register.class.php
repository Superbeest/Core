<?php
/**
* Register.class.php
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


namespace System\Register;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The register functions as a central register for the system. It can store information in
* a global way, and provides convenient handles to some of the base objects
* @package \System\Register
*/
class Register extends \System\Base\SingletonBase
{
    private $data = null;

    protected final function __construct()
    {
        $data = new \System\Collection\Map();
    }

    /**
    * Returns an entry from the register
    * @param string The index to look for
    * @return mixed The value we retrieved
    */
    public final function __get($index)
    {
        return $this->data[$index];
    }

    /**
    * Sets a value at the given index
    * @param string The index to place the given value at
    * @param mixed The value to store at the index
    */
    public final function __set($index, $value)
    {
        $this->data[$index] = $value;
    }

    /**
    * Checks if a given value exists in the register
    * @param string The index to check for
    * @return bool True if the index exists, false otherwise
    */
    public final function __isset($index)
    {
        return isset($this->data[$index]);
    }

    /**
    * Unsets the data at the given index
    * @param string The index to unset
    */
    public final function __unset($index)
    {
        unset($this->data[$index]);
    }
}
