<?php
/**
* Hash.class.php
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
* Contains functionality for hashing of values and files
* @package \System\Security
*/
class Hash extends \System\Base\BaseObj
{
    const HASH_MD5 = 'md5';
    const HASH_SHA1 = 'sha1';
    const HASH_SHA256 = 'sha256';
    const HASH_SHA512 = 'sha512';
    const HASH_WHIRLPOOL = 'whirlpool';
    const HASH_CRC32 = 'crc32';
    const HASH_SALSA20 = 'salsa20';

    /**
    * @var resource The hashing context
    */
    private $hash = null;
    /**
    * @var string Store the hash once its calculated
    */
    private $finalHash = '';

    /**
    * Constructs a new hash calculator using the given hashing method.
    * @param string The hashingmethod to be used
    */
    public final function __construct($hashingMethod = \System\Security\Hash::HASH_SHA1)
    {
        if (self::isHashAlgorithmInstalled($hashingMethod))
        {
            $this->hash = hash_init($hashingMethod);
        }
        else
        {
            throw new \InvalidArgumentException('Given hash method is not installed.');
        }
    }

    /**
    * Checks if the given hashing algorithm is installed on the system.
    *
    * @param string The given hashing method
    * @return bool True when installed, false otherwise
    */
    public static final function isHashAlgorithmInstalled($hashingMethod = \System\Security\Hash::HASH_SHA1)
    {
        $arr = hash_algos();
        return (in_array($hashingMethod, $arr));
    }

    /**
    * Adds the given file to the hash pool. The hash will be calculated over the total hash pool.
    * @param \System\IO\File The file to include in the hashing
    */
    public final function addFile(\System\IO\File $file)
    {
        if ($this->hash)
        {
            if ($file->exists())
            {
                hash_update_file($this->hash, $file->getFullPath());
            }
            else
            {
                throw new \System\Error\Exception\FileNotFoundException('The given file does not exists: ' . $file->getFilename());
            }
        }
        else
        {
            throw new \System\Error\Exception\InvalidMethodException('This method is called after finalizing the hash');
        }
    }

    /**
    * Adds the given string to the hash pool. The hash will be calculated over the total hash pool.
    * @param string The string to include in the hashing
    */
    public final function addString($string)
    {
        if ($this->hash)
        {
            hash_update($this->hash, $string);
        }
        else
        {
            throw new \System\Error\Exception\InvalidMethodException('This method is called after finalizing the hash');
        }
    }

    /**
    * Returns the hash in a finalized form. After calling this function, the hash can no longer be modified.
    * Calls to modifier methods, after calling getHash(), will result in an exception.
    * The returned hash is the result of the given hashing method.
    * @return string The hash of the given input variables.
    */
    public final function getHash()
    {
        if ($this->hash)
        {
            $this->finalHash = hash_final($this->hash);
            $this->hash = null;
        }

        return $this->finalHash;
    }
}
