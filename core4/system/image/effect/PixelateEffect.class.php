<?php
/**
* PixelateEffect.class.php
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
* Pixelates the current image using a pixelation percentage
* @package \System\Image\Effect
*/
class PixelateEffect extends \System\Image\Effect\ImageEffect
{
    /**
    * @var integer The pixelation percentage
    */
    private $pixelatePercentage;

    /**
    * Pixelates the current image using a pixelation percentage
    * @param int The pixelation percentage
    */
    public final function __construct($pixelatePercentage = 5)
    {
        $this->pixelatePercentage = $pixelatePercentage;
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

        $newImage = imagecreatetruecolor($width, $height);
        imagesavealpha($newImage, true);
        imagecopyresized($newImage, $imageData, 0, 0, 0, 0, ceil($width / $this->pixelatePercentage), ceil($height / $this->pixelatePercentage), $width, $height);
        imagecopyresized($imageData, $newImage, 0, 0, 0, 0, $width, $height, ceil($width / $this->pixelatePercentage), ceil($height / $this->pixelatePercentage));

        imagedestroy($newImage);

        return $imageData;
    }

    /**
    * Applies the image effect on an Imagick object. This function should be overridden by the effect.
    * @param \Imagick The image object to work with
    * @return \Imagick The new image object to use
    */
    protected function executeFilterImagick(\Imagick $imageData)
    {
        $width = $imageData->getImageWidth();
        $height = $imageData->getImageHeight();

        $imageData->resizeImage(ceil($width / $this->pixelatePercentage), ceil($height / $this->pixelatePercentage), \Imagick::FILTER_LANCZOS, 0, false);
        $imageData->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 0, false);

        return $imageData;
    }
}
