<?php
/**
* SessionHandler.struct.php
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
* Defines the available session handlers
* @package \System\HTTP\Storage
*/
class SessionHandler extends \System\Base\BaseStruct
{
    /**
    * The handler to store the session information in the regular local filesystem.
    * This is the default handler used by the system.
    */
    const HANDLER_FILES = 'files';

    /**
    * The handler to store the session information in the memcache
    */
    const HANDLER_MEMCACHE = 'memcache';
}