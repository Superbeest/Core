<?php
/**
* Map.class.php
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
* Map is a generic container for key/value data pairs.
* @package \System\Collection
*/
class Map extends \System\Collection\BaseMap
{
    /**
    * @var array The container for all the data. Do not access directly
    */
    protected $data = array();

    /**
    * @var string The type of data we allow to store (or null for all)
    */
    private $storeType = null;

    /**
    * Preoccupies the collection with data. This function supports different set of parameter types.
    * If there is one parameter, it supports a double, string, integer, array, or collection parameter.
    * The contents of the parameter is added to the current collection.
    * If there are multiple parameters, then only String, Double, Integer parameters are supported.

    * @param mixed Optional parameter for content filling
    * @param mixed Optional parameter for content filling
    * @param mixed ...
    */
    public function __construct()
    {
        if (func_num_args() == 1)
        {
            switch (\System\Type::getType(func_get_arg(0)))
            {
                case \System\Type::TYPE_DOUBLE:
                case \System\Type::TYPE_STRING:
                case \System\Type::TYPE_INTEGER:
                    $this[] = func_get_arg(0);
                    break;
                case \System\Type::TYPE_ARRAY:
                    $this->data = func_get_arg(0);
                    break;
                case \System\Type::TYPE_OBJECT:
                    switch (true)
                    {
                        case (func_get_arg(0) instanceof \System\Collection\iCollection):
                        case (func_get_arg(0) instanceof \System\Base\BaseStruct):
                        	$this->constructWithCollection(func_get_arg(0));
                            break;
                        default:
                            throw new \InvalidArgumentException('Invalid argument given. Expected Double, String, Integer, Array or Collection');
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid argument given. Expected Double, String, Integer, Array or Collection');
            }
        }
        else
        {
            foreach (func_get_args() as $arg)
            {
                switch (\System\Type::getType($arg))
                {
                    case \System\Type::TYPE_DOUBLE:
                    case \System\Type::TYPE_STRING:
                    case \System\Type::TYPE_INTEGER:
                        $this[] = $arg;
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid argument given. Expected Double, String, Integer, Array or Collection');
                }
            }
        }
    }

    /**
    * Creates a new collection datastructure with a given collection.
    * Its created as a new map.
    * @param mixed The collection to add
    */
    protected function constructWithCollection($collection)
    {
    	foreach ($collection as $index=>$value)
        {
            $this->data[$index] = $value;
        }
    }

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public function count()
    {
        return count($this->data);
    }

    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
    * Reverses the current collection.
    * @return \System\Collection\Map The current instance
    */
    public function reverse()
    {
        $this->data = array_reverse($this->data, true);

        return $this;
    }

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\iCollection\iCollection The collection to replace the current one with
    */
    public function exchangeCollection(\System\Collection\iCollection $input)
    {
        $this->data = $input->getArrayCopy();
    }

    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An array with the previous collection
    */
    public function exchangeArray(array $input)
    {
        $currentArray = $this->getArrayCopy();
        $this->data = $input;
        return $currentArray;
    }

    /**
    * Serializes the object
    * @return string The serialized object
    */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public function current()
    {
        return current($this->data);
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public function next()
    {
        next($this->data);
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public function key()
    {
        return key($this->data);
    }

    /**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public function valid()
    {
        return isset($this->data[$this->key()]);
    }

    /**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public function rewind()
    {
        reset($this->data);
    }

    /**
    * Checks if the given offset exists
    * @param mixed The index to check
    * @return boolean Returns whether or not he index exists
    */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index
    */
    public function offsetGet($offset)
    {
        $value = $this->keyExists($offset) ? $this->data[$offset] : null;

        return $value;
    }

    /**
    * Sets the value at the given offset
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    */
    public function offsetSet($offset, $value)
    {
        $offset = $this->getOffsetForSet($offset);
        if (($this->storeType != null) &&
            (!($value instanceof $this->storeType)))
        {
            throw new \InvalidArgumentException('Given value is not of the required ' . $this->storeType . ' type.');
        }

        $this->data[$offset] = $value;
    }

    /**
    * Removes the given value and index
    * @param mixed The index te remove
    */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
    * Sets the value type for the collection. All values that are stored in the collection, after this call,
    * must be of the given $type type. By setting the type to NULL, we accept everything again. This is the default.
    * Using this function we can enforce typed collections.
    * @param string The name of the type to accept, or null for all.
    */
    public function setValueType($type = null)
    {
        $this->storeType = $type;
    }
}