<?php
/**
* AESEncoding.class.php
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
* This class provides functionality for encoding and decoding AES encoded strings
* @package \System\Security
*/
class AESEncoding extends \System\Base\StaticBase
{
    /**
    * 128 bit cypher strenght
    */
    const CYPHER_128 = 128;
    /**
    * 256 bit cypher strenght
    */
    const CYPHER_256 = 256;

    /**
    * The IV for 256bit base64 encoded
    */
    const IV_256 = 'M/bPdpQEcrSyVDept68F3UlfRFqPKtylNFg3yF6yO0s=';
    /**
    * The IV for 128bit base64 encoded
    */
    const IV_128 = 'dUfyi3AWIDuJCM6C5+Xl6A==';

    /**
    * A random hash value to be used as a salt
    */
    const SALT = '4e7873912ebh69d4a0693e0f3625316d4';

	/**
	* Desired key length
	*/
    const KEY_LENGTH_16 = 16;
    /**
	* Desired key length
	*/
    const KEY_LENGTH_24 = 24;
    /**
	* Desired key length
	*/
    const KEY_LENGTH_32 = 32;

	/**
	* Padds the keys with 0's to make it compatible with the desired key lengths
	* Keys with lengths lower than these values are considered insecure
	* @param string The key to padd
	* @return string The padded key
	*/
	private static function paddKey($key)
	{
		if (strlen($key) <= self::KEY_LENGTH_16)
		{
			$desiredLength = self::KEY_LENGTH_16;
		}
		elseif (strlen($key) <= self::KEY_LENGTH_24)
		{
			$desiredLength = self::KEY_LENGTH_24;
		}
		elseif (strlen($key) <= self::KEY_LENGTH_32)
		{
			$desiredLength = self::KEY_LENGTH_32;
		}
		else
		{
			$desiredLength = self::KEY_LENGTH_32;
			$key = substr($key, 0, self::KEY_LENGTH_32);
		}

		while (strlen($key) < $desiredLength)
		{
			$key .= "\0";
		}

		return $key;
	}

    /**
    * Encodes the given string to a AES base64 encoded string
    * @param string The string to encode
    * @param string The key to use
    * @param int The strength to use. Use CYPHER_128 or CYPHER_256
    * @return string The base64 encoded AES encoded string
    */
    public static final function encode($string, $key, $cypherstrength = \System\Security\AESEncoding::CYPHER_128)
    {
        $encoded = '';
        $key = self::paddKey($key); //this is needed for backward compatibility, but is considered weak
        switch ($cypherstrength)
        {
            case self::CYPHER_128:
                $encoded = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, self::SALT . trim($string), MCRYPT_MODE_CFB, base64_decode(self::IV_128));
                break;
            case self::CYPHER_256:
                $encoded = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, self::SALT . trim($string), MCRYPT_MODE_CFB, base64_decode(self::IV_256));
                break;
            default:
                throw new \Exception('Invalid cypherstrength given');
        }

        return base64_encode($encoded);
    }

    /**
    * Decodes the given string from a AES base64 encoded string
    * @param string The string to decode
    * @param string The key to use
    * @param int The strength to use. Use CYPHER_128 or CYPHER_256
    * @return string The unencoded string. Will be trimmed as AES uses block padding
    */
    public static final function decode($string, $key, $cypherstrength = \System\Security\AESEncoding::CYPHER_128)
    {
        $string = base64_decode($string);
        $decoded = '';
        $key = self::paddKey($key); //this is needed for backward compatibility, but is considered weak
        switch ($cypherstrength)
        {
            case self::CYPHER_128:
                $decoded = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CFB, base64_decode(self::IV_128));
                break;
            case self::CYPHER_256:
                $decoded = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CFB, base64_decode(self::IV_256));
                break;
            default:
                throw new \Exception('Invalid cypherstrength given');
        }

        return trim(substr($decoded, strlen(self::SALT)));
    }
}
