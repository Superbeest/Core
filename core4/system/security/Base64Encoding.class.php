<?php
/**
* Base64Encoding.class.php
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


namespace System\Security;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Functions for encoding and decoding to base64.
* These functions use double base64 encoding to obfuscate the code a bit.
* Note that this form of encoding should not be considered as safe, but just as a form
* of simple obfuscation.
* @package \System\Security
*/
class Base64Encoding extends \System\Base\StaticBase
{
    /**
    * Encodes the given string. The key will be prepended to the string.
    * Double Base64 encoding is applied. This should not be considered safe encoding!
    * @param string The string to encode
    * @param string The key to prepend to the encoded string
    * @return string The encoded string
    */
    public static final function Base64Encode($string, $key)
    {
        $outputStr = $key . $string;

        $outputStr = base64_encode(base64_encode($outputStr));
        return $outputStr;
    }

    /**
    * Decodes the given string. This string must be double Base64 encoded.
    * The given key will be removed from the string.
    * @param string The string to decode
    * @param string The key to remove from the string
    * @return string The original string before encoding.
    */
    public static final function Base64Decode($string, $key)
    {
        $string = base64_decode(base64_decode($string));

        $outputStr = mb_substr($string, mb_strlen($key));

        return $outputStr;
    }
}