<?php
/**
* BaseMap.class.php
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
* This class implements the common base functionality for a associative map
* @package \System\Collection
*/
abstract class BaseMap extends \System\Base\BaseObj implements \System\Collection\iCollection
{
    /**
    * This variable stores the autoincrement pointer for the offsetSet function
    */
    protected $autoInc = 0;

    /**
    * Returns an entry from the collection using the given key.
    * @param mixed The key for the entry
    * @return mixed The mixed object at the given key, or a null on non-existing
    */
    public function get($key)
    {
        if (isset($this[$key]))
        {
            return $this[$key];
        }
        else
        {
            return null;
        }
    }

    /**
    * Returns an entry from the collection using the given key.
    * @param mixed The key for the entry
    * @return mixed The mixed object at the given key, or a null on non-existing
    */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
    * Sets, or overwrites, the given value to the data at position key.
    * Setting a value to NULL, effectively unsets the specified key
    * @param mixed The key to set the value at
    * @param mixed The value to set at the given key.
    * @return iCollection The current instance
    */
    public function set($key, $value)
    {
    	if (is_null($value))
    	{
			unset($this->$key);
		}
		else
		{
        	$this[$key] = $value;
		}
		return $this;
    }

    /**
    * Sets, or overwrites, the given value to the data at position key.
    * @param mixed The key to set the value at
    * @param mixed The value to set at the given key.
    * @return iCollection The current instance
    */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
    * Merges another collection into this collection.
    * This overwrites existing values with the same key
    * This function supports infinite parameters to merge
    * @param iCollection A collection to join in this one
    * @param iCollection Another collection to join
    * @param iCollection ...
    */
    public function combine(iCollection $collection)
    {
        foreach (func_get_args() as $arg)
        {
            if ($arg instanceof iCollection)
            {
                foreach ($arg as $key=>$value)
                {
                    $this[$key] = $value;
                }
            }
            else
            {
                throw new \InvalidArgumentException('invalid parameter given. Expecting only Collection (sub) instances.');
            }
        }
    }

    /**
    * Searches for the given value in the collections and returns if it is present or not.
    * @param mixed The value to search for
    * @param bool If the second parameter strict is set to TRUE, then the function will also check the types of the needle.
    * @return bool Boolean indicating if the object is present in the collection.
    */
    public function contains($needle, $strict = false)
    {
        return in_array($needle, $this->getArrayCopy(), $strict);
    }

    /**
    * Searches for the given value in the collections and returns if it is present or not.
    * @param mixed The value to search for
    * @param bool If the second parameter strict is set to TRUE, then the function will also check the types of the needle.
    * @return bool Boolean indicating if the object is present in the collection.
    * @see contains();
    */
    public final function hasItem($needle, $strict = false)
    {
        return $this->contains($needle, $strict);
    }

    /**
    * Searches for the givn alue in the collection and returns the corresponding key if it is present.
    * @param mixed The value to search for
    * @param bool If the second parameter strict is set to TRUE, then the function will also check the types of the needle.
    * @return mixed The corresponding key if present, or boolean false if not found.
    */
    public function getKey($needle, $strict = false)
    {
        return array_search($needle, $this->getArrayCopy(), $strict);
    }

    /**
    * Checks whether or not the given key exists in the collection.
    * @param mixed  The key to check for
    * @return bool A boolean representing key existance
    */
    public function keyExists($key)
    {
        return isset($this[$key]);
    }

    /**
    * Checks whether or not the given key exists in the collection.
    * @param mixed  The key to check for
    * @return bool A boolean representing key existance
    */
    public function __isset($key)
    {
        return isset($this[$key]);
    }

    /**
    * Removes a given key/value pair from the collection
    * @param mixed  The key of the key/value pair to be removed
    */
    public function __unset($key)
    {
        unset($this[$key]);
    }

    /**
    * Clears the collection, erasing everything in it.
    */
    public function clear()
    {
        $this->exchangeArray(array());
    }

    /**
    * Returns true if the Collection still contains items
    * @return bool Return true if there are items in the Collection, false otherwise.
    */
    public function hasItems()
    {
        return ($this->count() > 0);
    }

    /**
    * String representation of the current object
    * @return string A string representation of the current object
    */
    public function __toString()
    {
        return \System\Type::getClass($this) . ' (' . $this->count() . ')';
    }

    /**
    * Returns the proper offset for the given offset.
    * If the offset is null, thus not given, it returns the AutoInc.
    * @param mixed The offset given
    * @return mixed The proper offset
    */
    protected function getOffsetForSet($offset)
    {
		//we use the built in php gettype instead of the wrapper to increase the speed
        switch (gettype($offset))
        {
            case \System\Type::TYPE_INTEGER:
                if ($this->autoInc <= $offset)
                {
                    $this->autoInc = $offset + 1;
                }
                return $offset;
            case \System\Type::TYPE_STRING:
                switch (true)
                {
                    case strlen($offset) == 0:
                        throw new \System\Error\Exception\NullPointerException('Trying to allocate on a non-existing key-location');
                    case ctype_digit($offset):
                        if ($this->autoInc <= intval($offset))
                        {
                            $this->autoInc = $offset + 1;
                        }
                        break;
                    default:
                        //nothing special
                }
                return $offset;
            case \System\Type::TYPE_NULL:
                $val = $this->autoInc;
                $this->autoInc++;
                return $val;
            default:
                return $offset;
        }
    }
}