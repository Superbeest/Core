<?php
/**
* MessageType.struct.php
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


namespace System\System\Interaction;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the different MessageTypes
* @package \System\System\Interaction
*/
class MessageType extends \System\Base\BaseStruct
{
	const TYPE_NONE = 0;
	const TYPE_INVALIDATE = 1;
	const TYPE_SYSTEM = 2;
}