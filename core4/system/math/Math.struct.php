<?php
/**
* Math.struct.php
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


namespace System\Math;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The container struct with mathematical values
* @package \System\Math
*/
class Math extends \System\Base\BaseStruct
{
    /**
    * The maximum value of an integer, thus may differ on 64bit OS-es.
    * Usually this number is 2147483647 on 32bit platforms
    */
    const MAXINT                = PHP_INT_MAX;

    /**
    * Equal result value
    * a = b
    */
    const COMPARE_EQUAL         = 0;
    /**
    * Greater than result value
    * a > b
    */
    const COMPARE_GREATERTHAN   = 1;

    /**
    * Lesser than result value
    * a < b
    */
    const COMPARE_LESSTHAN      = -1;

    /**
    * The size of 1Mb in Kb
    */
    const SIZE_1M_IN_KBYTES     = 1024;

	/**
	* The size of 1Mb in bytes
	*/
    const SIZE_1M_IN_BYTES 		= 1048576;

    /**
    * The amount of bits in a byte
    */
    const BITS_PER_BYTE         = 8;

    /**
    * 8 bit max value in int
    */
    const BIT8                  = 255;

    /**
    * 8 bit max range in int
    */
    const BIT8_RANGE            = 256;

    /**
    * 16 bit integer unsigned, max value
    */
    const INT16_UNSIGNED		= 65535;

    /**
    * 16 bit integer signed, max value
    */
    const INT16_SIGNED			= 32767;

	/**
	* 32 bit integer unsigned, max value
	*/
    const INT32_UNSIGNED		= 4294967295;
    /**
    * 32 bit integer signed, max value
    */
    const INT32_SIGNED 			= 2147483647;
    /**
	* 32 bit integer unsigned, the amount of values
	*/
    const INT32_UNSIGNED_RANGE	= 4294967296;
    /**
    * 32 bit integer signed, the amount of values
    */
    const INT32_SIGNED_RANGE	= 2147483648;
}
