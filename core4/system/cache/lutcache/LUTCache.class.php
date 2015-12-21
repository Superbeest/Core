<?php
/**
* LUTCache.class.php
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


namespace System\Cache\LUTCache;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Caching mechanism for caching information in a DB field using a LUT technique
* @package \System\Cache\LUTCache
*/
class LUTCache extends \System\Base\StaticBase
{
    /**
    * The default timeout for a Memcache LUT storage
    */
    const CACHE_CACHE_MEMCACHE_TIMEOUT = 3600;

    /**
    * Stores the given value at the given key in the LUT.
    * @param \System\Db\Database The database
    * @param string The key to use for the LUT. Max 255 chars.
    * @param mixed The value to store in the LUT. This will be sanitized before storage
    */
    public static final function store(\System\Db\Database $db, $key, $value)
    {
        $query = new \System\Db\Query($db, \System\Cache\LUTCache\SQL_LUTCACHE_STORE);
        $query->bind(\System\Cache\LUTCache\Status::CACHE_HIT, \System\Db\QueryType::TYPE_INTEGER);
        $query->bind($key, \System\Db\QueryType::TYPE_STRING);
        $query->bind($value, \System\Db\QueryType::TYPE_STRING);
        $query->bind(\System\Cache\LUTCache\Status::CACHE_HIT, \System\Db\QueryType::TYPE_INTEGER);
        $query->bind($value, \System\Db\QueryType::TYPE_STRING);
        $db->query($query);

        self::setLUTCacheCache($key, $value);
    }

    /**
    * Invalidates the given value at the given key.
    * @param \System\Db\Database The database
    * @param string The key to use for the LUT. Max 255 chars.
    */
    public static final function invalidate(\System\Db\Database $db, $key)
    {
		self::setStatus($db, $key, \System\Cache\LUTCache\Status::CACHE_INVALIDATED);
		self::setLUTCacheCache($key, null);
    }

    /**
    * Invalidates the given value at the given key.
    * @param \System\Db\Database The database
    * @param string The key to use for the LUT. Max 255 chars.
    */
    public static final function setToGenerate(\System\Db\Database $db, $key)
    {
        self::setStatus($db, $key, \System\Cache\LUTCache\Status::CACHE_GENERATING);
        self::setLUTCacheCache($key, null);
    }

    /**
    * Sets the given value at the given key.
    * @param \System\Db\Database The database
    * @param string The key to use for the LUT. Max 255 chars.
    * @param int The status to set the key to
    */
    private static final function setStatus(\System\Db\Database $db, $key, $status)
    {
        $query = new \System\Db\Query($db, \System\Cache\LUTCache\SQL_LUTCACHE_STATUS);
        $query->bind($status, \System\Db\QueryType::TYPE_INTEGER);
        $query->bind($key, \System\Db\QueryType::TYPE_STRING);
        $db->query($query);
    }

    /**
    * Retrieve the stored value from the LUT.
    * @param \System\Db\Database The database
    * @param string The key to use for the LUT. Max 255 chars.
    * @param mixed The variable to store the result in. Defaults to NULL and only gives result on \System\Cache\LUTCache\Status::CACHE_HIT
    * @return int The return value for the function. See \System\Cache\LUTCache\Status::CACHE_HIT, \System\Cache\LUTCache\Status::CACHE_MISS, \System\Cache\LUTCache\Status::CACHE_GENERATING
    */
    public static final function retrieve(\System\Db\Database $db, $key, &$value = null)
    {
        if (self::getLUTCacheCache($key, $value))
        {
            return \System\Cache\LUTCache\Status::CACHE_HIT;
        }

        $query = new \System\Db\Query($db, \System\Cache\LUTCache\SQL_LUTCACHE_RETRIEVE);
        $query->bind($key, \System\Db\QueryType::TYPE_STRING);
        $results = $db->query($query);
        if ($results->count() == 0)
        {
            return Status::CACHE_MISS;
        }
        if ($results->current()->lutcache_status != \System\Cache\LUTCache\Status::CACHE_HIT)
        {
            return $results->current()->lutcache_status;
        }

        $value = $results->current()->lutcache_value;
        self::setLUTCacheCache($key, $value);
        return \System\Cache\LUTCache\Status::CACHE_HIT;
    }

    /**
    * Returns all currently stored values in the LUT cache.
    * @param \System\Db\Database The database to query
    * @return \System\Db\DatabaseResult A resultset with all the results.
    */
    public static final function getCache(\System\Db\Database $db)
    {
        $query = new \System\Db\Query($db, \System\Cache\LUTCache\SQL_LUTCACHE_RETRIEVE_ALL);
        $results = $db->query($query);

        return $results;
    }

    /**
    * Tries to retrieve a value from the LUTCache cache. This extra cache is build to relieve the LUTdb from strain.
    * The system currently supports both the Memcache as the APCCache.
    * @param string The key to use for the LUT. Max 255 chars.
    * @param mixed The variable to store the result in.
    * @return bool True on cache hit, false otherwise
    */
    private static final function getLUTCacheCache($key, &$value)
    {
        switch (LUTCACHE_CACHE)
        {
            case Types::CACHE_MEMCACHE:
                $mc = new \System\Cache\Memcache\Memcache();
                if (isset($mc->$key))
                {
                    $value = $mc->$key;
                    return true;
                }
                return false;
            case Types::CACHE_APC:
                $apc = new \System\Cache\APCCache\APCCache();
                if (isset($apc->$key))
                {
                    $value = $apc->$key;
                    return true;
                }
                return false;
            case Types::CACHE_NONE:
            default:
                return false;
        }
    }

    /**
    * Stores a value from the LUTCache to the LUTCacheCache to relieve strain.
    * @param string The key to use for the LUT. Max 255 chars.
    * @param mixed The value to store. If the value is NULL, the entry is unset.
    */
    private static final function setLUTCacheCache($key, $value = null)
    {
        switch (LUTCACHE_CACHE)
        {
            case Types::CACHE_MEMCACHE:
                $mc = new \System\Cache\Memcache\Memcache();
                if ($value === null)
                {
                    unset($mc->$key);
                }
                else
                {
                    $mc->store($key, $value, self::CACHE_CACHE_MEMCACHE_TIMEOUT);
                }
                break;
            case Types::CACHE_APC:
                $apc = new \System\Cache\APCCache\APCCache();
                if ($value === null)
                {
                    unset($apc->$key);
                }
                else
                {
                    $apc->$key = $value;
                }
                break;
            case Types::CACHE_NONE:
            default:
                break;
        }
    }
}

