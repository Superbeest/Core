<?php
/**
* Session.class.php
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
* This collection functions as a OO wrapper for the SESSION superglobal.
* It also sanitizes all the data automatically, so the user should not
* concern itself with this.
* @package \System\HTTP\Storage
*/
class Session extends \System\Collection\BaseMap
{
	/**
	* The default handler to use.
	*/
	const DEFAULT_HANDLER = \System\HTTP\Storage\SessionHandler::HANDLER_FILES;

	/**
	* @var string The current handler for the session system
	* @publicget
	*/
	protected static $currentHandler = self::DEFAULT_HANDLER;

	/**
	* @var \System\Base\BaseObj A custom handler for sessions
	*/
	protected static $customHandler = null;

	/**
	* @var bool Indicates whether or not the session headers have already been sent to the client.
	*/
    private static $headersSend = false;

    /**
    * The constructor initializes the session and sends the session headers
    * The session will be based on the given session handler. The session handler can be overridden by setting the SESSION_HANDLER definition.
    * Do note: if a handler fails to initialize, it will default to the DEFAULT_HANDLER.
    */
    public final function __construct()
    {
        if (!self::$headersSend)
        {
			//apply the session handler directive to our system
			if (!defined('SESSION_HANDLER'))
			{
				throw new \System\Error\Exception\SystemException('Invalid session handler given. Set to HANDLER_FILES; HANDLER_MEMCACHE; or a SessionHandler class');
			}

			self::$currentHandler = SESSION_HANDLER;

			switch (self::$currentHandler)
			{
				case SessionHandler::HANDLER_MEMCACHE:
					//initialize memcache and establish a connection. This makes sure the MEMCACHE_* settings are defined and correct.
					$memcache = new \System\Cache\Memcache\Memcache();
					//if we are not connected, we do a fallthrough to regular files.
					if ($memcache->isConnected())
					{
						ini_set('session.save_handler', 'memcache');

						$hosts = $ports = array();
						\System\Cache\Memcache\Memcache::getServers($hosts, $ports);
						$connectArray = array();
						foreach ($hosts as $index=>$host)
						{
							$connectArray[] = 'tcp://' . $host . ':' . $ports[$index];
						}

						//the save path expects something like 'tcp://IP:PORT'
						ini_set('session.save_path', implode(',', $connectArray));
						break;
					}

					//we do a fallthrough if the memcache is not connected and revert to files
					self::$currentHandler = SessionHandler::HANDLER_FILES;
				case SessionHandler::HANDLER_FILES:
					if (session_save_path() == '')
		            {
		                session_save_path(PATH_TEMP); //we use the temporary folder for this.
		            }
					break;
				default:
					if ((class_exists(self::$currentHandler)) &&
						(in_array('SessionHandlerInterface', class_implements(self::$currentHandler, true))))
					{
						self::$customHandler = new self::$currentHandler();
						session_set_save_handler(self::$customHandler, true);
					}
					else
					{
						throw new \System\Error\Exception\SystemException('Invalid session handler given: ' . self::$currentHandler);
					}
			}

			//this implicitly sends a few headers
            session_cache_limiter('nocache');
            session_cache_expire(180); //this is the default, but we set it explicitly

            session_name(SITE_IDENTIFIER . '_session');

            session_start();

            //we should only set our headers once
            self::$headersSend = true;
        }
    }

	/**
	* Regenerates the session id.
	* This does not invalidate the session itself and is used to prevent some session based attacks.
	* We do not delete the old session files to prevent double requests causing a logout.
	* @return bool True on success, false otherwise.
	*/
    public final function regenerateSession()
    {
    	return session_regenerate_id(false);
	}

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public final function count()
    {
        return count($_SESSION);
    }

    /**
    * Gets the current session id
    * @return string The current session id
    */
    public final function getSessionId()
    {
        return session_id();
    }

    /**
    * Sets a new session id and starts the session.
    * This closes the previous session.
    * @param string The new session id for the session. If the session exists, it gets opened.
    */
    public final function setSessionId($sessionId)
    {
        session_write_close();
        session_id($sessionId);
        session_start();
    }

    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public final function getArrayCopy()
    {
        return \System\Security\Sanitize::sanitizeString($_SESSION);
    }

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\Collection\iCollection The collection to replace the current one with
    */
    public final function exchangeCollection(\System\Collection\iCollection $input)
    {
        $_SESSION = $input->getArrayCopy();
    }

    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An array with the previous collection
    */
    public final function exchangeArray(array $input)
    {
        $currentArray = $this->getArrayCopy();
        $_SESSION = $input;
        return $currentArray;
    }

    /**
    * Serializes the object
    * @return string The serialized object
    */
    public final function serialize()
    {
        return serialize($_SESSION);
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public final function unserialize($serialized)
    {
        $_SESSION = unserialize($serialized);
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public final function current()
    {
        return current($_SESSION);
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public final function next()
    {
        next($_SESSION);
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public final function key()
    {
        return key($_SESSION);
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
        reset($_SESSION);
    }

    /**
    * Checks if the given offset exists
    * @param mixed The index to check
    * @return boolean Returns whether or not he index exists
    */
    public final function offsetExists($offset)
    {
        return isset($_SESSION[$offset]);
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index
    */
    public final function offsetGet($offset)
    {
        $value = $this->keyExists($offset) ? \System\Security\Sanitize::sanitizeString($_SESSION[$offset]) : null;

        return $value;
    }

    /**
    * Sets the value at the given offset
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    */
    public final function offsetSet($offset, $value)
    {
        $_SESSION[$offset] = $value;
    }

    /**
    * Removes the given value and index
    * @param mixed The index te remove
    */
    public final function offsetUnset($offset)
    {
        unset($_SESSION[$offset]);
    }
}