<?php
/**
* APCCache.class.php
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


namespace System\Cache\APCCache;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the APCCache interface for the system
* @package \System\Cache\APCCache
*/
class APCCache extends \System\Collection\BaseMap
{
    /**
    * The TTL for items in the user cache. Defaults to persistent storing.
    */
    const DEFAULT_STORE_TIME = 0;

    private static $apcIterator = null;

    /**
    * Build the APCCache object and prepare it for storing information
    */
    public final function __construct()
    {
        if (!function_exists('apc_cache_info'))
        {
            throw new \System\Error\Exception\APCCacheException('APC is not installed. Please setup APC before using the cache functionality.');
        }
    }

    private final function getAPCIterator()
    {
        if (self::$apcIterator == null)
        {
            self::$apcIterator = new \APCIterator(Type::TYPE_USER);
        }
        return self::$apcIterator;
    }

    /**
    * Clears the collection, erasing everything in it.
    */
    public function clear()
    {
        apc_clear_cache(Type::TYPE_USER);
    }

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public final function count()
    {
        $iterator = $this->getAPCIterator();
        return $iterator->getTotalCount();
    }

    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public final function getArrayCopy()
    {
        $iterator = $this->getAPCIterator();
        $arr = array();
        foreach ($iterator as $key=>$value)
        {
            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\Collection\iCollection The collection to replace the current one with
    */
    public final function exchangeCollection(\System\Collection\iCollection $input)
    {
        $this->exchangeArray($input->getArrayCopy());
    }

    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An empty array
    */
    public final function exchangeArray(array $input)
    {
        $this->clear();

        foreach ($input as $key=>$value)
        {
            $this->set($key, $value);
        }
        return array();
    }

    /**
    * Serializes the object
    * @return string The serialized object
    */
    public final function serialize()
    {
        throw new \System\Error\Exception\APCCacheException();
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public final function unserialize($serialized)
    {
        throw new \System\Error\Exception\APCCacheException();
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public final function current()
    {
        $iterator = $this->getAPCIterator();
        return $iterator->current();
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public final function next()
    {
        $iterator = $this->getAPCIterator();
        $iterator->next();
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public final function key()
    {
        $iterator = $this->getAPCIterator();
        return $iterator->key();
    }

    /**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public final function valid()
    {
        $iterator = $this->getAPCIterator();
        return $iterator->valid();
    }

    /**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public final function rewind()
    {
        $iterator = $this->getAPCIterator();
        $iterator->rewind();
    }

    /**
    * Generates the final key to be used to retrieve and store date in the resultset
    * @param string The key to use
    * @return string The final key to be used as index
    */
    private final function getIndexKey($key)
    {
        return SITE_IDENTIFIER . $key;
    }

    /**
    * Checks if the given offset exists
    * @param mixed The index to check
    * @return boolean Returns whether or not he index exists
    */
    public final function offsetExists($offset)
    {
		//check if the function apc_exists is in the current apc pecl build
		if (!function_exists('apc_exists'))
		{
			$result = false;
			apc_fetch($this->getIndexKey($offset), $result);
			return $result;
		}

        return (bool)apc_exists($this->getIndexKey($offset));
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index, or null if not found
    */
    public final function offsetGet($offset)
    {
        if ($this->keyExists($offset))
        {
            return apc_fetch($this->getIndexKey($offset));
        }

        return null;
    }

    /**
    * Stores a value in the memcache for a specified amount of time
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    * @param integer The amount of time to store the value in seconds. 0 is persistent.
    */
    public function store($key, $value, $timeout = self::DEFAULT_STORE_TIME)
    {
        apc_store($this->getIndexKey($key), $value, $timeout);
        self::$apcIterator = null;
    }

    /**
    * Sets the value at the given offset
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    */
    public final function offsetSet($offset, $value)
    {
        $this->store($offset, $value);
    }

    /**
    * Removes the given value and index
    * @param mixed The index te remove
    */
    public final function offsetUnset($offset)
    {
        apc_delete($this->getIndexKey($offset));
        self::$apcIterator = null;
    }

    /**
    * Loads global constants from the given key
    * @param mixed The index to be used
    */
    public final function loadConstants($key)
    {
        if ($this->keyExists($key))
        {
            apc_load_constants($this->getIndexKey($key));
        }
    }

    /**
    * Stores the given map constants to the APC
    * @param mixed The index to be used
    * @param \System\Collection\Map The constants to store. Use as key => value
    */
    public final function storeConstants($key, \System\Collection\Map $constants)
    {
        $this->storeConstantsArray($key, $constants->getArrayCopy());
    }

    /**
    * Stores the given array constants to the APC
    * @param mixed The index to be used
    * @param array The constants to store. Use as key => value
    */
    public final function storeConstantsArray($key, array $constants)
    {
        apc_define_constants($this->getIndexKey($key), $constants);
        self::$apcIterator = null;
    }

    /**
    * Gets server statistics from the APC
    * @return mixed A set of stats or false for failure
    */
    public final function getStats()
    {
        return apc_cache_info(Type::TYPE_USER);
    }
}
