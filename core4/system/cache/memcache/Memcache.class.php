<?php
/**
* Memcache.class.php
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


namespace System\Cache\Memcache;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the memcache interface for the system
* @package \System\Cache\Memcache
*/
class Memcache extends \System\Collection\BaseMap
{
    /**
    * The minimum value length before attempting to compress automatically
    */
    const MINIMUM_VALUE_LENGTH_FOR_COMPRESSION = 20000;
    /**
    * The minimum amount of savinds to actually store the value compressed. Must be between 0 and 1. 0.2 is default for 20% savings.
    */
    const COMPRESSION = 0.2;

    /**
    * The default amount of time to store an item in seconds.
    */
    const DEFAULT_STORE_TIME = 10;

    /**
    * @var \Memcache The memcache object
    */
    private static $memcache = null;

    /**
    * Build the memcache object and prepare it for storing information
    */
    public final function __construct()
    {
        if (self::$memcache == null)
        {
            \System\Version::registerRequiredConfigDirective('MEMCACHED_HOST');
            \System\Version::registerRequiredConfigDirective('MEMCACHED_PORT');
            \System\Version::registerRequiredConfigDirective('MEMCACHED_USE_COMPRESSION');
        }

        if ((!defined('MEMCACHED_HOST')) ||
            (!defined('MEMCACHED_PORT')) ||
            (!defined('MEMCACHED_USE_COMPRESSION')))
        {
            throw new \System\Error\Exception\SystemException('Invalid Memcache settings found. Please check your configuration.');
        }

        //by only checking for the initial null and not for false, we only try to boot the memcache once.
        if (self::$memcache === null)
        {
            try
            {
                //check if we want the memcache disabled
                if (MEMCACHED_HOST != '')
                {
                    if (!class_exists('\Memcache', false))
                    {
                        self::$memcache = false;
                    }
                    else
                    {
                        self::$memcache = new \Memcache();

						//we get all the servers and initialize those
						$hosts = $ports = array();
						$this->getServers($hosts, $ports);

						//add all the servers to the pool
						foreach ($hosts as $index=>$host)
						{
							//we dont use persistant connections
							$connected = self::$memcache->addServer($host, $ports[$index], false);
							if (!$connected)
							{
								//when one of the servers are not connected we stop
								self::$memcache = false;
								break;
							}
						}

						if (MEMCACHED_USE_COMPRESSION)
                        {
                            //compression system
                            self::$memcache->setCompressThreshold(self::MINIMUM_VALUE_LENGTH_FOR_COMPRESSION, self::COMPRESSION);
                        }
                    }
                }
                else
                {
                    self::$memcache = false;
                }
            }
            catch (\Exception $e)
            {
                self::$memcache = false;
                throw new \System\Error\Exception\MemcacheInvalidOperationException('Could not connect to the memcached server, or normal operation could not be resumed.');
            }
        }
    }

    /**
    * Gets all the servers, parsed from the MEMCACHED_HOST and MEMCACHED_PORT constants
    * @param array The hosts by reference
    * @param array The ports by reference
    */
	public static final function getServers(array &$hosts = array(), array &$ports = array())
	{
		$hosts = explode(',', MEMCACHED_HOST);
		$ports = explode(',', MEMCACHED_PORT);
		$ports = array_pad($ports, count($hosts), $ports[0]);
	}

    /**
    * Returns whether or not the system is connected to a memcached server.
    * @return boolean True when connected, false otherwise.
    */
    public final function isConnected()
    {
        return ((self::$memcache != null) &&
                (self::$memcache != false));
    }

    /**
    * Clears the collection, erasing everything in it.
    */
    public function clear()
    {
        if ($this->isConnected())
        {
            @self::$memcache->flush();
        }
    }

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public final function count()
    {
        if ($this->isConnected())
        {
            $stats = self::getStats();
            return $stats['curr_items'];
        }

        return 0;
    }

    /**
    * Creates an array with the contents of the collection
    * @return array A new array with the contents of the collection
    */
    public final function getArrayCopy()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
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
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public final function unserialize($serialized)
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public final function current()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public final function next()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public final function key()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public final function valid()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
    }

    /**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public final function rewind()
    {
        throw new \System\Error\Exception\MemcacheInvalidOperationException();
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
        if ($this->isConnected())
        {
            return (bool)@self::$memcache->get($this->getIndexKey($offset));
        }

        return false;
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index, or null if not found
    */
    public final function offsetGet($offset)
    {
        if (($this->isConnected()) &&
            ($this->keyExists($offset)))
        {
            return @unserialize(@self::$memcache->get($this->getIndexKey($offset)));
        }

        return null;
    }

    /**
    * Stores a value in the memcache for a specified amount of time
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    * @param integer The amount of time to store the value in seconds
    */
    public function store($key, $value, $timeout = self::DEFAULT_STORE_TIME)
    {
        if ($this->isConnected())
        {
            @self::$memcache->set($this->getIndexKey($key), serialize($value), MEMCACHED_USE_COMPRESSION, $timeout);
        }
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
        if ($this->isConnected())
        {
            @self::$memcache->delete($this->getIndexKey($offset));
        }
    }

    /**
    * Returns the current version number of the memcached server.
    * @return mixed A string containing the version number, or integer 0
    */
    public final function getVersion()
    {
        if ($this->isConnected())
        {
            $val = self::$memcache->getVersion();
            if ($val)
            {
                return $val;
            }
        }

        return 0;
    }

    /**
    * Gets server statistics from the memcached server
    * @return array A set of stats or false for failure
    */
    public final function getStats()
    {
        if ($this->isConnected())
        {
            return self::$memcache->getStats();
        }

        return false;
    }

    /**
    * Gets extended server statistics from the memcached server with all the connected servers
    * @return array A set of stats or false for failure
    */
    public final function getExtendedStats()
    {
        if ($this->isConnected())
        {
            return self::$memcache->getExtendedStats();
        }

        return false;
    }

    /**
    * Gets server status from all the connected memcached servers.
    * Each key in the array is the server address, with a value of 1 for successfull operation, 0 otherwise
    * @return array A set of stats or false for failure
    */
    public final function getServerStatus()
    {
        if ($this->isConnected())
        {
			$hosts = $ports = array();
			$this->getServers($hosts, $ports);

			$arr = array();
			foreach ($hosts as $index=>$host)
			{
				$arr[$host . ':' . $ports[$index]] = self::$memcache->getServerStatus($host, $ports[$index]);
			}

            return $arr;
        }

        return false;
    }
}
