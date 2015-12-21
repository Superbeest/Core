<?php
/**
* OnXOREncodedEvent.class.php
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


namespace System\Event\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* An Event raised when the XOREncoded function terminates
* @package \System\Event\Event
*/
class OnXOREncodedEvent extends \System\Event\EventHandler
{
    /**
    * Constructs the object and connects the hooks to the event
    */
    public function __construct()
    {
        \System\Inspection\Hook::register('\System\Security\XOREncoding::XOREncrypt', array($this, 'raise'), \System\Inspection\Hook::HOOK_POST);
    }
}
