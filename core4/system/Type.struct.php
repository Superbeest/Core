<?php
/**
* Type.struct.php
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


namespace System;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Container class that contains functions for types, type juggling and string representations
* @package \System
*/
class Type extends \System\Base\BaseStruct
{
    /**
    * Boolean type
    */
    const TYPE_BOOLEAN  = 'boolean';
    /**
    * Boolean type
    */
    const TYPE_BOOL		= 'bool';
    /**
    * Integer type
    */
    const TYPE_INTEGER  = 'integer';
    /**
    * Integer type
    */
    const TYPE_INT  	= 'int';
    /**
    * Double type
    */
    const TYPE_DOUBLE   = 'double';
    /**
    * String type
    */
    const TYPE_STRING   = 'string';
    /**
    * Array type
    */
    const TYPE_ARRAY    = 'array';
    /**
    * Object type
    */
    const TYPE_OBJECT   = 'object';
    /**
    * Resource type
    */
    const TYPE_RESOURCE = 'resource';
    /**
    * NULL
    */
    const TYPE_NULL     = 'NULL';
    /**
    * Timestamp type, self defined
    */
    const TYPE_TIMESTAMP = 'timestamp';
	/**
	* Serialized type, self defined
	*/
    const TYPE_SERIALIZED = 'serialized';
    /**
    * Unknown type
    */
    const TYPE_UNKNOWN  = 'unknown type';

    /**
    * Returns a string representation of the given parameter.
    * If the given parameter is an object, we first try to get the class' own representation of the object using the toString() function.
    * @param mixed A value to get a string representation of
    * @return string A string representation of the given parameter or 'Unknown' for unknown types
    */
    public static final function getValue($value)
    {
        $retVal = 'Unknown';
        switch (true)
        {
            case is_array($value):
                $retVal = serialize($value);
                break;
            case is_bool($value):
                $retVal = $value ? 'true' : 'false';
                break;
            case is_float($value):
            case is_int($value):
            case is_string($value):
                $retVal = $value;
                break;
            case is_object($value):
                //if the given value is an object, first try to get the toString result
                if ((method_exists($value, '__toString')) &&
                    (is_callable(array($value, '__toString'))))
                {
                    $retVal = call_user_func(array($value, '__toString'));
                }
                else
                {
                    $retVal = self::getClass($value);
                }
                $retVal .= '(' . self::getObjectId($value) . ')';
                break;
            case is_null($value):
                $retVal = 'NULL';
                break;
            case is_resource($value):
                $retVal = get_resource_type($value);
                break;
            default:
                break;
        }

        return $retVal;
    }

    /**
    * Returns the type of the given parameter in a string.
    * @param mixed A value to get the type of.
    * @return string The type of the given parameter, or 'unknown type'.
    */
    public static final function getType($value)
    {
        return gettype($value);
    }

    /**
    * Converts a given callback to a readable string. After this conversion, the string may not be usable as a callback anymore.
    * Note that this function does not verify the correctness of the given callback.
    * @param callback The callback to convert
    * @return string The string representation of the callback
    */
    public static final function callbackToString($callback)
    {
        if (is_array($callback))
        {
            if (count($callback) == 2)
            {
                list($class, $method) = $callback;
                if ((is_string($class)) &&
                    (is_string($method)))
                {
                    return $class . '::' . $method;
                }
                else if ((is_object($class)) &&
                         (is_string($method)))
                {
                    return (string)$class . '->' . $method;
                }
                else
                {
                    throw new \InvalidArgumentException('The given callback is not a valid callback');
                }
            }
            else
            {
                throw new \InvalidArgumentException('The given callback is not a valid callback');
            }
        }
        else if (is_string($callback))
        {
            return $callback;
        }
        else
        {
            throw new \InvalidArgumentException('The given callback is not a valid callback');
        }
    }

    /**
    * Returns the unique id of the object. This id is unique for every object and can be used as a index key.
    * Do note that once the object gets unset, its id is released and can be re-issued.
    * @param object The object to get the id from
    * @return string The id for the given object.
    */
    public static final function getObjectId($instance)
    {
        if (\System\Type::getType($instance) == \System\Type::TYPE_OBJECT)
        {
            return spl_object_hash($instance);
        }
        else
        {
            throw new \InvalidArgumentException('The given parameter is not an instance of an object.');
        }
    }

    /**
    * Returns the name of an object instance
    * @param mixed The instance of the object
    * @param bool True to only return the name of the class, excluding namespaces
    * @return string The name of the instance
    */
    public static final function getClass($instance, $excludeNamespace = false)
    {
        $classname = '\\' . get_class($instance);

        if ($excludeNamespace)
        {
			$pieces = explode('\\', $classname);
        	$classname = end($pieces);
		}

        return $classname;
    }
}