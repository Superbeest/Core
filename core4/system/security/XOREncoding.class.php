<?php
/**
* XOREncoding.class.php
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
* Provides support for XOR encoding. Note that this form of encoding does not provide
* a solid way of dataprotection.
* @package \System\Security
*/
class XOREncoding extends \System\Base\StaticBase
{
	/**
	* The prepend string to autoappend to the key
	*/
	const KEY_PREPEND = '7873912ebh69d4a0693e0f';

    /**
    * Does the actual encryption.
    * @param string The string to encrypt/decrypt
    * @param string The key to use
    * @return string The encrypted string
    */
    protected static final function XOREncryption($string, $key)
    {
		$key = self::KEY_PREPEND . $key;
        $keyLength = mb_strlen($key);
        for ($i = 0; $i < mb_strlen($string); $i++)
        {
            $charPos = $i % $keyLength;
            $r = ord($string[$i]) ^ ord($key[$charPos]);
            $string[$i] = chr($r);
        }

        return $string;
    }

    /**
    * Applies XOR and Base64 encoding to the given string.
    * This should not be considered a safe form of encoding.
    * @param string The string to encode
    * @param string The key to to use
    * @return string The encrypted string
    */
    public static final function XOREncrypt($string, $key)
    {
        return base64_encode(self::XOREncryption($string, $key));
    }

    /**
    * Decrypts a Base64 encoded and XOR encoded string to a human readable format
    * @param string The Base64 and XOR encoded string
    * @param string The key to use for decoding
    * @return string The original string.
    */
    public static final function XORDecrypt($string, $key)
    {
        return self::XOREncryption(base64_decode($string), $key);
    }
}