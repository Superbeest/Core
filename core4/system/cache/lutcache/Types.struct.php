<?php
/**
* Types.struct.php
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
* Defines the types of the LUTCache
* @package \System\Cache\LUTCache
*/
class Types extends \System\Base\BaseStruct
{
    /**
    * The LUTCache does not get cached
    */
    const CACHE_NONE = 0;
    /**
    * The LUTCache uses Memcache to speed up the LUT
    */
    const CACHE_MEMCACHE = 1;
    /**
    * The LUTCache uses APCCache to speed up the LUT
    */
    const CACHE_APC = 2;
}