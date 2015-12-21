<?php
/**
* HTTPSQLFlag.struct.php
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


namespace System\Db\HTTP;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the HTTP SQL Flags
* @package \System\Db\HTTP
*/
class HTTPSQLFlag extends \System\Base\BitStruct
{
	const NOT_NULL = 1;
	const PRIMARY_KEY = 2;
	const UNIQUE_KEY = 4;
	const MULTIPLE_KEY = 8;
	const BLOB = 16;
	const UNSIGNED = 32;
	const ZEROFILL = 64;
	const BINARY = 128;
	const ENUM = 256;
	const AUTO_INCREMENT = 512;
	const TIMESTAMP = 1024;
	const SET = 2048;

	/**
	* Gets an array with types in the given value
	* @param int The value set
	* @return array An array with types present
	*/
	public static function getFlagsFromVal($val)
	{
		$flags = new HTTPSQLFlag();

		$arr = array();
		foreach ($flags as $name=>$flag)
		{
			if (self::contains($val, $flag))
			{
				$arr[] = $flag;
			}
		}

		return $arr;
	}
}