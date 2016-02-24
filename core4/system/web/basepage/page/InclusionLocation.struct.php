<?php
/**
* InclusionLocation.struct.php
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


namespace System\Web\BasePage\Page;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* Defines the location of the included files
* @package \System\Web\BasePage\Page
*/
class InclusionLocation extends \System\Base\BaseStruct
{
    /**
     * Places the included file in the head
     */
    const LOCATION_HEAD = 0;

    /**
     *  Places the included file at the end of the body
     */
    const LOCATION_BODY_END = 1;

    /**
     * Places the included file outside, below the HTML tags
     */
    const LOCATION_OUTSIDE_HTML = 2;
}