<?php
/**
* SecureMap.class.php
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


namespace System\Collection;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* SecureMap is a generic container for key/value data pairs. The content in this
* map is always sanitized.
* @package \System\Collection
*/
class SecureMap extends \System\Collection\Map
{
    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public function getArrayCopy()
    {
        return \System\Security\Sanitize::sanitizeString($this->data);
    }
    
    /**
    * Retrieves the value from the given index.
    * A map does NOT encode the '&' symbol.
    * @param mixed The index to return
    * @return mixed The value on that specific index
    */
    public function offsetGet($offset)
    {
        $value = $this->keyExists($offset) ? \System\Security\Sanitize::sanitizeString($this->data[$offset], false) : null;

        return $value;
    }
}