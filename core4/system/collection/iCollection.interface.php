<?php
/**
* iCollection.interface.php
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
* The base interface for all the collections. This makes the collections tick.
* @package \System\Collection
*/
interface iCollection extends \Countable, \Iterator, \ArrayAccess, \Serializable
{
    /**
    * Returns an entry from the collection using the given key.
    * @param mixed  The key for the entry
    * @return mixed The mixed object at the given key, or a null on non-existing
    */
    public function get($key);
    /**
    * Sets, or overwrites, the given value to the data at position key.
    * @param mixed  The key to set the value at
    * @param mixed  The value to set at the given key.
    * @return iCollection The current instance
    */
    public function set($key, $value);
    /**
    * Returns an entry from the collection using the given key.
    * @param mixed  The key for the entry
    * @return mixed The mixed object at the given key, or a null on non-existing
    */
    public function __get($key);
    /**
    * Sets, or overwrites, the given value to the data at position key.
    * @param mixed  The key to set the value at
    * @param mixed  The value to set at the given key.
    * @return iCollection The current instance
    */
    public function __set($key, $value);

    /**
    * Checks whether or not the given key exists in the collection.
    * @param mixed  The key to check for
    * @return bool A boolean representing key existance
    */
    public function keyExists($key);
    /**
    * Checks whether or not the given key exists in the collection.
    * @param mixed  The key to check for
    * @return bool A boolean representing key existance
    */
    public function __isset($key);
    /**
    * Removes a given key/value pair from the collection
    * @param mixed  The key of the key/value pair to be removed
    */
    public function __unset($key);

    /**
    * Clears the collection, erasing everything in it.
    */
    public function clear();
    /**
    * Returns true if the Collection still contains items
    * @return bool Return true if there are items in the Collection, false otherwise.
    */
    public function hasItems();

    /**
    * Searches for the given value in the collections and returns if it is present or not.
    * @param mixed The value to search for
    * @param bool If the second parameter strict is set to TRUE, then the function will also check the types of the needle.
    * @return bool Boolean indicating if the object is present in the collection.
    */
    public function contains($needle, $strict = false);
    /**
    * Merges another collection into this collection.
    * This overwrites existing values with the same key
    * This function supports infinite parameters to merge
    * @param iCollection A collection to join in this one
    * @param iCollection Another collection to join
    * @param iCollection ...
    */
    public function combine(iCollection $collection);

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\Collection\iCollection The collection to replace the current one with
    */
    public function exchangeCollection(iCollection $collection);
    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An array with the previous collection
    */
    public function exchangeArray(array $input);
    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public function getArrayCopy();
}
