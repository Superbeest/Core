<?php
/**
* VectorTrait.trait.php
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
* Implements the vector functionality for Vector and SecureVector
* @package \System\Collection
*/
trait VectorTrait
{
    /**
    * Merges another collection into this collection.
    * This function differs from its parent implementation in the sense that it expands the
    * Vector with the contents of the parameters, instead of overwriting on the same key
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
                foreach ($arg as $value)
                {
                    $this[] = $value;
                }
            }
            else
            {
                throw new \InvalidArgumentException('invalid parameter given. Expecting only Collection (sub) instances.');
            }
        }
    }

    /**
    * Creates a new collection datastructure with a given collection.
    * Its created as a new vector.
    * @param mixed The collection to add
    */
    protected function constructWithCollection($collection)
    {
    	foreach ($collection as $value)
        {
            $this[] = $value;
        }
	}

	/**
	* Returns a subset of the given collection in the stored order. The order is preserved.
	* When more items are requested than available, the result will be shortened.
	* @param int The first index to return
	* @param int The amount of items to return
	* @return Vector A new collection with the given items.
	*/
    public function slice($offset, $length)
    {
    	$data = $this->getArrayCopy();
    	return new \System\Collection\Vector(array_slice($data, $offset, $length, false));
	}

    /**
    * Shuffles the entire Vector. Do note that all the contents is reordered and any index positions are changed.
    * @return \System\Collection\Vector The current instance
    */
    public function shuffle()
    {
        $data = $this->getArrayCopy();
        shuffle($data);
        $this->exchangeArray($data);

        return $this;
    }

    /**
    * Reverses the current collection.
    * @return \System\Collection\Vector The current instance
    */
    public function reverse()
    {
        $data = $this->getArrayCopy();
        $data = array_reverse($data, false);
        $this->exchangeArray($data);

        return $this;
    }

    /**
    * Returns the proper offset for the given offset.
    * If the offset is null, thus not given, it returns the AutoInc.
    * @param mixed The offset given
    * @return mixed The proper offset
    */
    protected function getOffsetForSet($offset)
    {
        switch (gettype($offset))
        {
            case \System\Type::TYPE_INTEGER:
                if ($this->autoInc <= $offset)
                {
                    $this->autoInc = $offset + 1;
                }
                return $offset;
            case \System\Type::TYPE_STRING:
                if (ctype_digit($offset))
                {
                    if ($this->autoInc <= intval($offset))
                    {
                        $this->autoInc = $offset + 1;
                    }
                    return $offset;
                }
                throw new \Exception('Invalid offset for Vector type: ' . $offset);
            case \System\Type::TYPE_NULL:
                $val = $this->autoInc;
                $this->autoInc++;
                return $val;
            default:
                throw new \Exception('Invalid offset for Vector type');
        }
    }

	/**
	* Returns the first element. Note that this function rewinds the current iteration pointers
	* @return mixed The first element in the Vector
	*/
    public function first()
    {
    	$this->rewind();
    	return $this->current();
    }

    /**
    * Gets the last element from the collection
    * @return mixed The last element in the Vector
    */
    public function last()
    {
        $element = null;

        //iterate to the last element
        foreach ($this as $element)
        {
            //do nothing
            continue;
        }

        return $element;
}

    /**
    * Because of PHP's lack of non-associative arrays, this function can rebuild the index to check for a
    * complete incremental digit index and rebuilds all indices.
    * This will invalidate all current known indices.
    */
    public function rebuildIndex()
    {
    	$data = $this->getArrayCopy();
        $data = array_values($data);

        //we set the autoInc to the current last index
        end($data);
        $this->autoInc = (int)key($data);

        $this->exchangeArray($data);

        return $this;
    }

	/**
    * Removes a given key/value pair from the collection. This rebuilds the current index
    * @param mixed The key of the key/value pair to be removed
    */
    public function __unset($key)
    {
        parent::__unset($key);
        $this->rebuildIndex();
    }

    /**
    * Converts the data in the Vector to string, separated by the given separator.
    * Note: The values in the Vector must be of string type.
    * @param string The string to use as separator
    * @return string The outputted string
    */
    public function convertToString($separator = ', ')
    {
        return implode($separator, $this->getArrayCopy());
    }

    /**
    * Adds a value to the bottom of the Vector
    * @param mixed The value to add
    */
    public function add($value)
    {
        $this[] = $value;
    }
}