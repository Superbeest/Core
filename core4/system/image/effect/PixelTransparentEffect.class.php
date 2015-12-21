<?php
/**
* PixelTransparentEffect.class.php
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
* Makes the current image transparent using the color of the given pixel
* @package \System\Image\Effect
*/
class PixelTransparentEffect extends \System\Image\Effect\ImageEffect
{
    /**
    * @var int The X coordinate of the pixel
    */
    private $pixelX = 0;
    /**
    * @var int The Y coordinate of the pixel
    */
    private $pixelY = 0;

    /**
    * @var int The fuzzyness factor to compare two colors
    */
    private $fuzzyNess = 1000;

    /**
    * Creates the PixelTransparentEffect object.
    * @param int The pixel X position to use as transparent pixelcolor
    * @param int The pixel Y position to use as transparent pixelcolor
    * @param int The amount of colors around the given pixelcolor to make transparent aswell
    */
    public final function __construct($pixelX = 0, $pixelY = 0, $fuzzyNess = 1000)
    {
        $this->pixelX = $pixelX;
        $this->pixelY = $pixelY;
        $this->fuzzyNess = $fuzzyNess;
    }

    /**
    * Applies the image effect on a GD resource. This function should be overridden by the effect.
    * @param resource The image resource to work with
    * @return resource The resource to use
    */
    protected final function executeFilterGD($imageData)
    {
        throw new \System\Error\Exception\MethodDoesNotExistsException('This filter is not supported by GD. Please use the Imagick\Image class');
    }

    /**
    * Applies the image effect on an Imagick object. This function should be overridden by the effect.
    * @param \Imagick The image object to work with
    * @return \Imagick The new image object to use
    */
    protected function executeFilterImagick(\Imagick $imageData)
    {
        $imagePixel = $imageData->getImagePixelColor($this->pixelX, $this->pixelY);
        $imageData->paintTransparentImage($imagePixel, 0, $this->fuzzyNess);

        return $imageData;
    }
}
