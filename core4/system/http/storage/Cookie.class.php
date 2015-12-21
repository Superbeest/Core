<?php
/**
* Cookie.class.php
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


namespace System\HTTP\Storage;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This collection functions as a OO wrapper for the COOKIE superglobal.
* It also sanitizes all the data automatically, so the user should not
* concern itself with this.
* @package \System\HTTP\Storage
*/
class Cookie extends \System\Collection\BaseMap
{
    /**
    * Empty constructor to prevent data inclusion
    */
    public final function __construct()
    {
        if (!isset($_COOKIE[SITE_IDENTIFIER]))
        {
            $_COOKIE[SITE_IDENTIFIER] = array();
        }
    }

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public final function count()
    {
        return count($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public final function getArrayCopy()
    {
        return \System\Security\Sanitize::sanitizeString($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\Collection\iCollection The collection to replace the current one with
    */
    public final function exchangeCollection(\System\Collection\iCollection $input)
    {
        $_COOKIE[SITE_IDENTIFIER] = $input->getArrayCopy();
    }

    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An array with the previous collection
    */
    public final function exchangeArray(array $input)
    {
        $currentArray = $this->getArrayCopy();
        $_COOKIE[SITE_IDENTIFIER] = $input;
        return $currentArray;
    }

    /**
    * Serializes the object
    * @return string The serialized object
    */
    public final function serialize()
    {
        return serialize($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public final function unserialize($serialized)
    {
        throw new \Exception('This method is not supported by cookies.');
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public final function current()
    {
        return current($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public final function next()
    {
        next($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public final function key()
    {
        return key($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public final function valid()
    {
        return isset($_SESSION[$this->key()]);
    }

    /**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public final function rewind()
    {
        reset($_COOKIE[SITE_IDENTIFIER]);
    }

    /**
    * Checks if the given offset exists
    * @param mixed The index to check
    * @return boolean Returns whether or not he index exists
    */
    public final function offsetExists($offset)
    {
        return isset($_COOKIE[SITE_IDENTIFIER][$offset]);
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index
    */
    public final function offsetGet($offset)
    {
        $value = $this->keyExists($offset) ? \System\Security\Sanitize::sanitizeString($_COOKIE[SITE_IDENTIFIER][$offset]) : null;

        return $value;
    }

    /**
    * Sets the value at the given offset
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    */
    public final function offsetSet($offset, $value)
    {
        setcookie(SITE_IDENTIFIER . '[' . $offset . ']', $value, time() + (3600 * 24 * 365), '/');
        $_COOKIE[SITE_IDENTIFIER][$offset] = $value;
    }

    /**
    * Unsets the given value at the corresponding index
    * @param mixed The index
    */
    public final function offsetUnset($index)
    {
        setcookie(SITE_IDENTIFIER . '[' . $index . ']', '', time() - 3600, '/');
        unset($_COOKIE[SITE_IDENTIFIER][$index]);
    }
}