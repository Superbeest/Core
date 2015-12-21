<?php
/**
* PermaBan.class.php
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


namespace System\HTTP\Visitor\PermaBan;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Provides functionality to check if a given IP is blocked by a common blacklist
* @package \System\HTTP\Visitor\PermaBan
*/
class PermaBan extends \System\Base\StaticBase
{
	/**
	* The memcache key to use for the permaban
	*/
	const MEMCACHE_KEY = 'permaban_';

    /**
    * Provides functionality to check if a given IP is blocked by a common blacklist
    * Do note this system requires the use of the PERMABAN_* directives
    * @param string The IP Address to check.
    * @return bool True if the IP is allowed, false otherwise
    */
    public static final function isIPAllowed($ipAddress)
    {
		//if there is an explicit empty PERMABAN, we accept everything
		if (PERMABAN_HOST == '')
		{
			return true;
		}

		$allowed = true;

		$mc = new \System\Cache\Memcache\Memcache();

		$key = self::MEMCACHE_KEY . $ipAddress;

		//we get the value from the memcache, and only recheck if the blocked user is on it.
		if (!$allowed = $mc->get($key))
		{
	        $db = \System\Db\Database::getConnection(PERMABAN_HOST, PERMABAN_USER, PERMABAN_PASS, PERMABAN_NAME, PERMABAN_PORT);

	        $query = new \System\Db\Query($db, \System\HTTP\Visitor\PermaBan\SQL_PERMABAN_CHECK_PERMABAN);
	        $query->bind($ipAddress, \System\Db\QueryType::TYPE_STRING);

	        $results = $db->query($query);

	        $allowed = ($results->count() == 0);

	        $mc->store($key, $allowed);
		}

		return $allowed;
    }
}