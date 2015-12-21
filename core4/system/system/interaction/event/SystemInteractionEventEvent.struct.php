<?php
/**
* SystemInteractionEventEvent.struct.php
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


namespace System\System\Interaction\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the built-in SystemInteractionEvent events
* @package \System\System\Interaction\Event
*/
class SystemInteractionEventEvent extends \System\Base\BaseStruct
{
	const EVENT_OPCACHE_RESET = 'opcachereset';
	const EVENT_INVALIDATE_STATICCACHE_ENTRY = 'invalidatestaticcacheentry';
	const EVENT_GET_CONSTANT = 'constant';
	const EVENT_GET_LOADED_MODULES = 'loadedmodules';
	const EVENT_SHELL_EXEC = 'shellexec';
	const EVENT_DELETE_FILE = 'deletefile';
	const EVENT_SEND_FILE = 'sendfile';
	const EVENT_RECEIVE_FILE = 'file';
	const EVENT_DB_FINGERPRINT = 'dbfingerprint';
	const EVENT_FILE_FINGERPRINT = 'filefingerprint';
	const EVENT_QUERY = 'query';
	const EVENT_CLEAN_PAGECACHE = 'cleanpagecache';
}
