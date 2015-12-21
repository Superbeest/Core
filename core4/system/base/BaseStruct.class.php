<?php
/**
* BaseStruct.class.php
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
* The base class for all structures in the system
* @package \System\Base
*/
class BaseStruct implements \System\Base\iBaseObj, \Iterator
{
	/**
	* @var array The reflection constants get stored in here, used internally.
	*/
    private $reflectionConstants = array();

    /**
    * Enforce the magic PHP function toString. It enforces a string representation of the object
    * @return string A string representation of the object
    */
    public final function toString()
    {
        return $this->__toString();
    }

    /**
    * Enforce the magic PHP function toString. It enforces a string representation of the object
    * @return string A string representation of the object
    */
    public final function __toString()
    {
        return $this->getClassName();
    }

    /**
	* Returns the current classname, including namespaces
	* @return string The name of the class
	*/
	public final function getClassName()
	{
		return \System\Type::getClass($this);
	}

	/**
	* Returns the current classname, excluding namespaces
	* @return string The name of the class
	*/
	public final function getBaseClassName()
	{
		return \System\Type::getClass($this, true);
	}

	/**
	* Returns the name of the current static class. This resolves using late static binding
	* and thus returns the name from the class it was called on.
	* @return string The name of the class
	*/
    public static final function getStaticClassName()
    {
    	return '\\' . get_called_class();
	}

	/**
	* Returns the name of the current static class. This resolves using late static binding, excluding namespaces
	* and thus returns the name from the class it was called on.
	* @return string The name of the class
	*/
    public static final function getStaticBaseClassName()
    {
    	$pieces = explode('\\', '\\' . get_called_class());
        return end($pieces);
	}

    /**
    * Empty constructor to prohibit class extending
    */
    public final function __construct()
    {
        $reflectionClass = new \ReflectionClass($this);
        $this->reflectionConstants = $reflectionClass->getConstants();
    }

    /**
    * Returns the lowest value in the structure.
    * @return mixed The lowest value in the structure, or null
    */
    public function getLowest()
    {
        reset($this->reflectionConstants);
        $lowestValue = current($this->reflectionConstants);
        foreach ($this->reflectionConstants as $constant)
        {
            if ($constant < $lowestValue)
            {
                $lowestValue = $constant;
            }
        }

        return $lowestValue;
    }

    /**
    * Returns the highest value in the structure
    * @return mixed The highest value in the structure, or null
    */
    public function getHighest()
    {
        reset($this->reflectionConstants);
        $highestValue = current($this->reflectionConstants);
        foreach ($this->reflectionConstants as $constant)
        {
            if ($constant > $highestValue)
            {
                $highestValue = $constant;
            }
        }

        return $highestValue;
    }

    public final function getNameForValue($value)
    {
        foreach ($this as $name=>$valueConst)
        {
            if ($valueConst === $value)
            {
                return $name;
            }
        }

        return null;
    }

    /**
    * Return the current element
    * @return mixed Return the current element
    */
    public final function current()
    {
        return current($this->reflectionConstants);
    }

    /**
    * Return the key of the current element
    * @param string Return the key of the current element
    */
    public final function key()
    {
        return key($this->reflectionConstants);
    }

    /**
    * Move forward to next element
    */
    public final function next()
    {
        next($this->reflectionConstants);
    }

    /**
    * Rewind the Iterator to the first element
    */
    public final function rewind()
    {
        reset($this->reflectionConstants);
    }

    /**
    * Checks if current position is valid
    * @return bool Checks if current position is valid
    */
    public final function valid()
    {
        return isset($this->reflectionConstants[$this->key()]);
    }
}
