<?php
/**
* Pisces.class.php
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


namespace System\Calendar\Starsign;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* A container class to define the Pisces starsign
* @package \System\Calendar\Starsign
*/
class Pisces extends \System\Calendar\Starsign\Starsign
{
    /**
    * contstructor sets the default starsign parameters
    */
    public final function __construct()
    {
        $this->startTime = new \System\Calendar\Time(2, 20);
        $this->stopTime = new \System\Calendar\Time(3, 20);
    }
}