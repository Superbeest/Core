<?php
/**
* OS.class.php
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


namespace System\Server;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Container class for OS information retrieval functions
* @package \System\Server
*/
class OS extends \System\Base\BaseStruct
{
    /**
    * Windows operating systems
    */
    const OS_WINDOWS = 1;
    /**
    * Unix like operating systems
    */
    const OS_UNIX = 2;

    /**
    * Returns the current OS
    * @return int \System\IO\OS::OS_WINDOWS for a Windows platform, or \System\IO\OS::OS_UNIX for others
    */
    public final static function getOS()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? self::OS_WINDOWS : self::OS_UNIX);
    }
}