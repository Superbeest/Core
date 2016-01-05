<?php
/**
* SecureVector.class.php
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
* The SecureVector class is non-assocative.
* It is implemented as a non-associative collection using only nummeric
* keys for its values.
* The contents is secured
* This is enforced by the overridden offsetSet function
* @package \System\Collection
*/
class SecureVector extends \System\Collection\SecureMap
{
    use VectorTrait;
    
    /**
    * Returns a subset of the given collection in the stored order. The order is preserved.
    * When more items are requested than available, the result will be shortened.
    * @param int The first index to return
    * @param int The amount of items to return
    * @return SecureVector A new collection with the given items.
    */
    public function slice($offset, $length)
    {
    	$data = $this->getArrayCopy();
    	return new \System\Collection\SecureVector(array_slice($data, $offset, $length, false));
    }
}
