<?php
/**
* BaseObj.class.php
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
* This class functions as the base class for most objects
* @package \System\Base
*/
class BaseObj implements \System\Base\iBaseObj
{
    /**
    * Defines the default variable declaration to match with
    */
    const PUBLICSET_VARIABLE_DECLARATION = '@var ';

    /**
    * The function cache for instances contexts
    */
    private $functionCache = null;
    /**
    * The function cache from a static context
    */
    private static $functionCacheStatic = null;

    /**
    * String representation of the current object
    * @return string A string representation of the current object
    */
    public function __toString()
    {
        return $this->getClassName();
    }

    /**
    * String representation of the current object
    * @return string A string representation of the current object
    */
    public function toString()
    {
        return $this->__toString();
    }

    /**
    * Returns an empty stdClass. Extend to implement your own behaviour
    * @param array The properties of the current object
    * @return \stdClass An empty object
    */
    public static function __set_state(array $properties)
    {
    	return new \stdClass();
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
    * Implements specific function call methods. These methods are available for all the base inheritence classes.
    * Currently, we have implemented support for:
    * - function hooking; this enables an aspect oriented coding style
    * - function result caching; this dramatically improves program execution
    * - function timing; this times the execution of a specific function. Do note that the result is encapsulated in a Map.
    * - function memory measurement; this calculates the amount of memory needed for a specific function. Do note that the result is encapsulated in a Map.
    * Note: Calling functions using the caching method, these functions need to be at least protected or public, and can not use referenced parameters.
    * Note: This function should not be called directly, and should be used only in the __call function.
    * @param string The name of the function to call
    * @param array  An array containing all the arguments
    * @param mixed The context from the call, a string in case of static context, the object reference when called as an instance
    * @param mixed The returnvalue of the function is stored here.
    * @return boolean Returns true when a handler is called, false otherwise.
    */
	protected static final function __callDefaultHandlers($name, array &$arguments, $sourceContext, &$returnValue)
	{
		/*
        upon extension of this function, the \System\Inspection\APIGen::addClassMethods should also be extended
        */

		switch (true)
        {
            //we support function hooking
            case mb_substr($name, 0, 2) == 'h_':
                //we remove the 'h_' character from the functionname to call the original
                $returnValue = \System\Inspection\Hook::call(array($sourceContext, mb_substr($name, 2)), $arguments);
                return true;
            //we support function timing
            case mb_substr($name, 0, 2) == 't_':
                $col = new \System\Collection\Map();
                $time = \System\Inspection\Timer::timeCall(array($sourceContext, mb_substr($name, 2)), $returnVal, $arguments);
                $col['time'] = $time;
                $col['result'] = $returnVal;
                $returnValue = $col;
                return true;
            //we support function memory measurement
            case mb_substr($name, 0, 2) == 'm_':
                $returnValue = \System\Inspection\Memory::measureCall(array($sourceContext, mb_substr($name, 2)), $returnVal, $arguments);
                $returnValue['result'] = $returnVal;
                return true;
            //we support function result caching.
            case mb_substr($name, 0, 2) == 'c_':
                //we remove the 'c_' character from the functionname to call the original
                if (is_object($sourceContext))
                {
                	/* do note: static calls from instance context are cached in the instance, and called as such */
                	$returnValue = $sourceContext->functionCache(mb_substr($name, 2), $arguments); //from an instance context
				}
				else
				{
					$returnValue = self::functionCacheStatic(mb_substr($name, 2), $arguments); //from a static context
				}
                return true;
            case mb_substr($name, 0, 2) == 'is':
            	$propertyName = lcfirst(mb_substr($name, 2));

            	if (property_exists($sourceContext, $propertyName))
                {
                	$reflectionProperty = new \ReflectionProperty($sourceContext, $propertyName);
                	if ((mb_stripos($reflectionProperty->getDocComment(), '@publicget') !== false) &&
                		(mb_stripos($reflectionProperty->getDocComment(), '@var bool') !== false) && //this works for bool, boolean
                        (($reflectionProperty->isPublic()) || ($reflectionProperty->isProtected())))
                    {
                    	$returnValue = (!$reflectionProperty->isStatic()) ? (bool)$sourceContext->$propertyName : (bool)static::$$propertyName;
                    	return true;
					}
				}

            	break;
            case mb_substr($name, 0, 3) == 'get':
                $propertyName = lcfirst(mb_substr($name, 3));

                if (property_exists($sourceContext, $propertyName))
                {
                	$reflectionProperty = new \ReflectionProperty($sourceContext, $propertyName);
                	if ((mb_stripos($reflectionProperty->getDocComment(), '@publicget') !== false) &&
                        (($reflectionProperty->isPublic()) || ($reflectionProperty->isProtected())))
                    {
                    	//when the @validatehandle comment is set, we look at the @var comment to create a new map and ensure validity
                    	if (mb_stripos($reflectionProperty->getDocComment(), '@validatehandle') !== false)
						{
							$typeDef = self::getFunctionParamType($reflectionProperty);
							if (($typeDef = self::getFunctionParamType($reflectionProperty)) &&
								(is_subclass_of($typeDef, '\System\Collection\BaseMap')))
							{
								if (!$reflectionProperty->isStatic()) //instance context
								{
									if (!$sourceContext->$propertyName instanceof \System\Collection\BaseMap) //we force the inheritance
									{
										$sourceContext->$propertyName = new $typeDef;
									}
								}
								else //static context
								{
									if (!static::$$propertyName)
									{
										static::$$propertyName = new $typeDef;
									}
								}
							}
							else
							{
								throw new \System\Error\Exception\InvalidMethodException('@validatehandle is defined for ' . $name . '(), but the parameter type is not given or not a decendant of \System\Collection\BaseMap');
							}
						}

						$returnValue = (!$reflectionProperty->isStatic()) ? $sourceContext->$propertyName : static::$$propertyName;
                    	return true;
					}
				}

                break;
            case mb_substr($name, 0, 3) == 'set':
                $propertyName = lcfirst(mb_substr($name, 3));

                if ((count($arguments) == 1) &&
	            	(property_exists($sourceContext, $propertyName)))
	            {
	            	$propertyName = lcfirst(mb_substr($name, 3));
	                $reflectionProperty = new \ReflectionProperty($sourceContext, $propertyName);
	                if ((mb_stripos($reflectionProperty->getDocComment(), '@publicset') !== false) &&
	                    (($reflectionProperty->isPublic()) || ($reflectionProperty->isProtected())) &&
	                    (self::checkPublicSetTypeEquality($reflectionProperty, $arguments[0])))
	                {
	                	if (!$reflectionProperty->isStatic()) //instance context
	                	{
	                		$sourceContext->$propertyName = $arguments[0];
	                        $returnValue = $sourceContext;
						}
						else //static context
						{
							static::$$propertyName = $arguments[0];
	                        $returnValue = static::$$propertyName; //static functions cannot return an instance object, and thus return the value set
						}
						return true;
					}
				}
                break;
		}

		return false;
	}

    /**
    * Gets the given type from the documentation.
    * When the type cannot be discovered, false is returned.
    * @param \ReflectionProperty The reflection object about the property
    * @return string The type of the PUBLICSET_VARIABLE_DECLARATION declaration, or false if not found.
    */
    private static final function getFunctionParamType(\ReflectionProperty $property)
    {
    	$doc = $property->getDocComment();

    	$pos = mb_stripos($doc, self::PUBLICSET_VARIABLE_DECLARATION);
    	if ($pos !== false)
        {
            //the structure of the @var line is simple: @var <type> <description>. We are only interested in the <type>-def.
            $matches = array();
            if (preg_match('/(.*)$/im', mb_substr($doc, ($pos + strlen(self::PUBLICSET_VARIABLE_DECLARATION))), $matches))
            {
				$words = explode(' ', $matches[1]);
		        if (($words) &&
		            (is_array($words)) &&
		            (count($words) > 0))
		        {
	        		$typeDef = trim($words[0]);
	        		return $typeDef;
				}
			}
        }

        return false;
	}

    /**
    * Checks for the presence of the @var declaration in the doccomment.
    * If this comment is present, then we force the type of the argument.
    * @param \ReflectionProperty The property
    * @param mixed The argument passed
    * @return bool True if valid parameter, false otherwise
    */
    private static final function checkPublicSetTypeEquality(\ReflectionProperty $property, $argument)
    {
        $typeDef = self::getFunctionParamType($property);

		if ($typeDef)
		{
            //we default to a faulty type
            $typeOk = false;
            $typeGiven = '';

            //mixed type parameters accept everything.
            if ($typeDef == 'mixed')
            {
                return true;
            }

            switch (\System\Type::getType($argument))
            {
                case \System\Type::TYPE_ARRAY:
                    $typeOk = (strtolower($typeDef) == \System\Type::TYPE_ARRAY);
                    $typeGiven = \System\Type::TYPE_ARRAY;
                    break;
                case \System\Type::TYPE_BOOL:
                case \System\Type::TYPE_BOOLEAN:
                    $typeOk = ((strtolower($typeDef == \System\Type::TYPE_BOOLEAN)) ||
                               (strtolower($typeDef) == \System\Type::TYPE_BOOL));
                    $typeGiven = \System\Type::TYPE_BOOLEAN;
                    break;
                case \System\Type::TYPE_DOUBLE:
                    $typeOk = (strtolower($typeDef) == \System\Type::TYPE_DOUBLE);
                    $typeGiven = \System\Type::TYPE_DOUBLE;
                    break;
                case \System\Type::TYPE_INTEGER:
                case \System\Type::TYPE_INT:
                    $typeOk = ((strtolower($typeDef == \System\Type::TYPE_INTEGER)) ||
                               (strtolower($typeDef) == \System\Type::TYPE_INT));
                    $typeGiven = \System\Type::TYPE_INTEGER;
                    break;
                case \System\Type::TYPE_OBJECT:
                    $typeOk = ($argument instanceof $typeDef);
                    $typeGiven = get_class($argument);
                    break;
                case \System\Type::TYPE_RESOURCE:
                    $typeOk = (strtolower($typeDef) == \System\Type::TYPE_RESOURCE);
                    $typeGiven = \System\Type::TYPE_RESOURCE;
                    break;
                case \System\Type::TYPE_STRING:
                    $typeOk = (strtolower($typeDef) == \System\Type::TYPE_STRING);
                    $typeGiven = \System\Type::TYPE_STRING;
                    break;
                case \System\Type::TYPE_NULL:
                case \System\Type::TYPE_TYPE_UNKNOWN:
                default:
                    $typeGiven = 'mixed';
                    $typeOk = false;
            }

            if (!$typeOk)
            {
                throw new \InvalidArgumentException('Argument is of wrong datatype. Expected: \'' . $typeDef . '\', \'' . $typeGiven . '\' given.');
            }
        }

        return true;
    }

    /**
    * Adds support for specific function call methods.
    * By overloading this function new functionality can be added.
    * The base class __call implementation uses the __callDefaultHandlers() function.
    * @param string The name of the function to call
    * @param array  An array containing all the arguments
    * @return mixed The return value of the called function
    */
    public function __call($name, array $arguments)
    {
        $returnVal = false;
        if ((self::__callDefaultHandlers($name, $arguments, $this, $returnVal)) ||
        	(self::__callDefaultHandlers($name, $arguments, get_class($this), $returnVal))) //also support static calls from an instance context
        {
            return $returnVal;
        }

        throw new \System\Error\Exception\MethodDoesNotExistsException('Method ' . get_class($this) . '->' . $name . '() does not exists in the current context');
    }

    /**
    * Adds support for specific function call methods.
    * By overloading this function new functionality can be added.
    * This function makes use of the __callStaticDefaultHandlers function.
    * This function is a built in PHP magic function and should not be called directly.
    * @param string The name of the function to call
    * @param array  An array containing all the arguments
    * @return mixed The return value of the called function
    * @see __call()
    */
    public static function __callStatic($name, array $arguments)
    {
        $returnVal = false;
        if (self::__callDefaultHandlers($name, $arguments, get_called_class(), $returnVal))
        {
            return $returnVal;
        }

        throw new \System\Error\Exception\MethodDoesNotExistsException('Method ' . get_called_class() . '::' . $name . '() does not exists in the current context');
    }

    /**
    * Implements the function result value caching for static functions. This function is called by the __callStatic
    * function and generates a key from the given parameters. It performs a simple and fast lookup
    * in an internal array and returns the value if needed. Otherwise the value is appended to the
    * lut by executing the function and storing its result.
    * Note: it is not possible to call functions that require parameters by reference
    * @param string The name of the function being called
    * @param array The arguments passed to the function
    * @return mixed The result of the function
    */
    private static final function functionCacheStatic($name, array $arguments)
    {
        //first we need a lut key from the given params
        $key = get_called_class() . '_' . $name . '(';

        //serialize the parameters
        foreach ($arguments as $arg)
        {
            $key .= \System\Type::getValue($arg);
        }

        $key .= ')';

        //because we dont have a constructor, we must check the type of the cache and create it
        //if needed
        if (self::$functionCacheStatic === null)
        {
            self::$functionCacheStatic = new \System\Collection\Map();
        }

        //lookup the key in the functionCache for fast calling and store the function result
        if (!isset(self::$functionCacheStatic[$key]))
        {
            if (is_callable(array(get_called_class(), $name)))
            {
                self::$functionCacheStatic[$key] = call_user_func_array(array(get_called_class(), $name), $arguments);
            }
            else
            {
                throw new \System\Error\Exception\InvalidMethodException('Cannot access given method. Is it public or protected?: ' . $name);
            }
        }
        return self::$functionCacheStatic[$key];
    }

	/**
	* Does nothing from an instance context
        * @return bool Always true
	*/
    public final function doNothing()
    {
        return true;
	}

	/**
	* Does nothing from a static context
        * @return bool Always true
	*/
	public static final function doNothingStatic()
	{
            return true;
	}

    /**
    * Implements the function result value caching. This function is called by the __call
    * function and generates a key from the given parameters. It performs a simple and fast lookup
    * in an internal array and returns the value if needed. Otherwise the value is appended to the
    * lut by executing the function and storing its result.
    * Note: it is not possible to call functions that require parameters by reference
    * @param string The name of the function being called
    * @param array The arguments passed to the function
    * @return mixed The result of the function
    */
    private final function functionCache($name, array $arguments)
    {
        //first we need a lut key from the given params
        $key = spl_object_hash($this) . '_' . $name . '(';

        //serialize the parameters
        foreach ($arguments as $arg)
        {
            $key .= \System\Type::getValue($arg);
        }

        $key .= ')';

        //because we dont have a constructor, we must check the type of the cache and create it
        //if needed
        if ($this->functionCache === null)
        {
            $this->functionCache = new \System\Collection\Map();
        }

        //lookup the key in the functionCache for fast calling and store the function result
        if (!isset($this->functionCache[$key]))
        {
            if (is_callable(array($this, $name)))
            {
                $this->functionCache[$key] = call_user_func_array(array($this, $name), $arguments);
            }
            else
            {
                throw new \System\Error\Exception\InvalidMethodException('Cannot access given method. Is it public or protected?: ' . $name);
            }
        }
        return $this->functionCache[$key];
    }
}
