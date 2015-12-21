<?php
/**
* ValidationOptions.struct.php
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


namespace Module\HTMLForm\FormBuilder;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* The validation options
* @package \Module\HTMLForm\FormBuilder
*/
class ValidationOptions extends \System\Base\BitStruct
{
    const VALIDATE_NONE = 0;

    const VALIDATE_EMAIL = 1;

    const VALIDATE_NOTEMPTY = 2;

    const VALIDATE_PHONE = 4;

    const VALIDATE_ZIP_NL = 8;

    const VALIDATE_NUMBERS = 16;

    const VALIDATE_URL = 32;
}

