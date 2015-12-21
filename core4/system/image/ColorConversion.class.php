<?php
/**
* ColorConversion.class.php
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


namespace System\Image;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This class holds functionality to convert color values to different types
* @package \System\Image
*/
class ColorConversion extends \System\Base\StaticBase
{
    /**
    * Converts a HEX color (3 or 6 digits, in- or excluding #) to RGB colors.
    * @param string 3 or 6 digit hex code, optionally preceded by #
    * @param integer The red component by reference
    * @param integer The green component by reference
    * @param integer The blue component by reference
    * @return boolean True on succes, false otherwise
    */
    public static final function hexToRGB($hex, &$r = 0, &$g = 0, &$b = 0)
    {
        $val = new \System\Security\Validate();
        if ($val->isHexColor($hex, 'color', true) != \System\Security\ValidateResult::VALIDATE_OK)
        {
            return false;
        }
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3)
        {
            $r = hexdec(substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1));
            $r *= $r;
            $g *= $g;
            $b *= $b;
        }
        else
        {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return true;
    }

    /**
    * Converts a RGB code to hex value, preceded by #
    * @param integer The red component
    * @param integer The green component
    * @param integer The blue component
    * @param string The converted hex value
    * @return boolean True on succes, false otherwise
    */
    public static final function RGBToHex($r, $g, $b, &$hex = '')
    {
        $val = new \System\Security\Validate();
        $val->isInt($r, 'red', 0, 255, true);
        $val->isInt($g, 'green', 0, 255, true);
        $val->isInt($b, 'blue', 0, 255, true);

        if ($val->isInputOk())
        {
            $hex = '#';
            $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
            $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
            $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
            return true;
        }

        return false;
    }
}
