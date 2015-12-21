<?php
/**
* LUTCache.sql.php
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
* @package \System\Cache\LUTCache
*/

const SQL_LUTCACHE_STORE = 'INSERT INTO lutcache (lutcache_status, lutcache_key, lutcache_value) VALUES (%?%, %?%, %?%) ON DUPLICATE KEY UPDATE lutcache_status = %?%, lutcache_value = %?%';
const SQL_LUTCACHE_RETRIEVE = 'SELECT lutcache_status, lutcache_value, lutcache_key FROM lutcache WHERE lutcache_key = %?% LIMIT 1';
const SQL_LUTCACHE_STATUS = 'UPDATE lutcache SET lutcache_status = %?% WHERE lutcache_key = %?%';
const SQL_LUTCACHE_RETRIEVE_ALL = 'SELECT lutcache_key AS lutkey, lutcache_value AS value, lutcache_status AS status FROM lutcache';