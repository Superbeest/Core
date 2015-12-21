<?php
/**
* ImageEffect.class.php
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


namespace System\Image\Effect;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Serves as the base for the image effects.
* @package \System\Image\Effect
*/
abstract class ImageEffect extends \System\Base\BaseObj
{
    /**
    * This function executes the effect on the given image and its internal datastructure.
    * This function should not be called manually. For applying effects, see the applyEffect function in the given image.
    * @see \System\Image\Image::applyEffect()
    * @param \System\Image\Image An image or a subclass
    * @param mixed The image datastructure to work with
    */
    public final function executeFilter(\System\Image\Image $image, $imageData)
    {
    	$imagickClass = '\System\Image\Imagick\Image';
		if ($image instanceof $imagickClass)
		{
			return $this->executeFilterImagick($imageData);
		}
		else if ($image instanceof \System\Image\Image)
		{
			return $this->executeFilterGD($imageData);
		}
		else
		{
			throw new \System\Error\Exception\InvalidMethodException('Cannot execute a filter on the given input type: ' . \System\Type::getClass($image));
		}
    }

    /**
    * Applies the image effect on a GD resource. This function should be overridden by the effect.
    * @param resource The image resource to work with
    * @return resource The resource to use
    */
    protected function executeFilterGD($imageData)
    {
        throw new \System\Error\Exception\MethodNotImplementedException();
    }

    /**
    * Applies the image effect on an Imagick object. This function should be overridden by the effect.
    * @param \Imagick The image object to work with
    * @return \Imagick The new image object to use
    */
    protected function executeFilterImagick(\Imagick $imageData)
    {
        throw new \System\Error\Exception\MethodNotImplementedException();
    }
}