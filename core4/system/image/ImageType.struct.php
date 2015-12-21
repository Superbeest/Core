<?php
/**
* ImageType.struct.php
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
* The base structure for the primary imagetypes
* @package \System\Image
*/
class ImageType extends \System\Base\BaseStruct
{
    /**
    * JPEG File type
    */
    const TYPE_JPEG = 1;

    /**
    * GIF File type
    */
    const TYPE_GIF = 2;

    /**
    * PNG File type
    */
    const TYPE_PNG = 3;

	/**
	* Returns the default extension for the given ImageType value
	* Returns excluding point and in lowercase
	* @param int The ImageType value to return the corresponding extension of
	* @return string The extension, excluding point and in lowercase
	*/
    public static function getExtension($type)
    {
    	switch ($type)
    	{
    		case self::TYPE_JPEG:
    			return 'jpg';
    		case self::TYPE_PNG:
    			return 'png';
    		case self::TYPE_GIF:
    			return 'gif';
    		default:
    			throw new \System\Error\Exception\InvalidArgumentException('The given type is not a valid ImageType');
		}
	}
}