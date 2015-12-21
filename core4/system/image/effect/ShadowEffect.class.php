<?php
/**
* ShadowEffect.class.php
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
* Applies a shadow border effect to the image
* @package \System\Image\Effect
*/
class ShadowEffect extends \System\Image\Effect\ImageEffect
{
    /**
    * @var integer The width of the shadowborder
    */
    private $shadowWidth;

    /**
    * Applies a shadow border effect to the image
    * @param integer The width of the shadowborder in pixels
    */
    public final function __construct($shadowWidth = 5)
    {
        $this->shadowWidth = $shadowWidth;
    }

    /**
    * Applies the image effect on a GD resource. This function should be overridden by the effect.
    * @param resource The image resource to work with
    * @return resource The resource to use
    */
    protected final function executeFilterGD($imageData)
    {
        $width = imagesx($imageData);
        $height = imagesy($imageData);

        //resize the canvas
        $image = imagecreatetruecolor($width + $this->shadowWidth, $height + $this->shadowWidth);
        imagesavealpha($image, true);

        //set the background
        $transparentColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0 , 0, $transparentColor);

        //copy the original image into the newimage
        imagecopy($image, $imageData, 0, 0, 0, 0, $width, $height);

        $transparencyStep = 127 / $this->shadowWidth;
        for ($x = 0; $x < $this->shadowWidth; $x++)
        {
            $alpha = intval(round($transparencyStep * $x));
            $color = imagecolorallocatealpha($image, 128, 128, 128, $alpha);
            imageline($image, $width + $x, $this->shadowWidth, $width + $x, $height - 1 + $x, $color);
            imageline($image, $this->shadowWidth, $height + $x, $width + $x, $height + $x, $color);
        }

        imagedestroy($imageData);
        return $image;
    }

    /**
    * Applies the image effect on an Imagick object. This function should be overridden by the effect.
    * @param \Imagick The image object to work with
    * @return \Imagick The new image object to use
    */
    protected final function executeFilterImagick(\Imagick $imageData)
    {
        $shadow = $imageData->clone();
        $shadow->setImageBackgroundColor(new \ImagickPixel('black'));

        $shadow->shadowImage(80, $this->shadowWidth / 4, 0, 0);

        $shadow->compositeImage($imageData, \Imagick::COMPOSITE_OVER, 0, 0);


        return $shadow;
    }
}