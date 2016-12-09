<?php
/**
* ReflectionEffect.class.php
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
* Applies a nice reflection effect to the image
* @package \System\Image\Effect
*/
class ReflectionEffect extends \System\Image\Effect\ImageEffect
{
    /**
    * @var string A valid hex color string or an empty string for transparent
    */
    private $reflectionBackgroundColor;
    /**
    * @var int The height of the reflection
    */
    private $reflectionHeight;
    /**
    * @var int The distance between the gradient and the image
    */
    private $gradientDistance;

    /**
    * Adds a reflection to the image. This reflection is added to the bottom of the image.
    * It uses a transparency effect and should be outputted as a PNG image.
    * This function uses per pixel manipulation and is therefor not extremely fast. It should be used sparingly.
    * If the $reflectionBackgroundColor is a valid hexcolor, then this color is used instead of alpha transparency.
    * @param int The height of the reflection
    * @param string A valid hex color string or an empty string for transparent
    * @param int The distance between the gradient and the image
    */
    public final function __construct($reflectionHeight = 100, $reflectionBackgroundColor = '', $gradientDistance = 1)
    {
        $this->reflectionHeight = $reflectionHeight;
        $this->reflectionBackgroundColor = $reflectionBackgroundColor;
        $this->gradientDistance = $gradientDistance;
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

        //we make sure we cannot go out of bounds
        if ($this->reflectionHeight > $height)
        {
            $this->reflectionHeight = $height;
        }

        //create a new image with alpha channels
        $image = imagecreatetruecolor($width, $height + $this->reflectionHeight + $this->gradientDistance - 1);
        imagesavealpha($image, true);

        //use the proper color for the background and fill
        if (!empty($this->reflectionBackgroundColor))
        {
            $val = new \System\Security\Validate();
            if ($val->isHexColor($this->reflectionBackgroundColor, 'color', true) == \System\Security\ValidateResult::VALIDATE_OK)
            {
                $r = 0;
                $g = 0;
                $b = 0;
                \System\Image\ColorConversion::hexToRGB($this->reflectionBackgroundColor, $r, $g, $b);
                $transparentColor = imagecolorallocatealpha($image, $r, $g, $b, 0);
            }
        }
        else
        {
            $transparentColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
        }
        imagefill($image, 0 , 0, $transparentColor);

        //copy the original image into the newimage
        imagecopy($image, $imageData, 0, 0, 0, 0, $width, $height);

        $transparencyStep = 127 / $this->reflectionHeight;
        //copy the bottom $reflectionHeight pixels of the image and make them transparent
        for ($y = $height - 1; $y >= ($height - $this->reflectionHeight); $y--)
        {
            for ($x = 0; $x < $width; $x++)
            {
                $colors = imagecolorsforindex($imageData, imagecolorat($imageData, $x, $y));
                $alpha = intval(round($transparencyStep * ($height - $y)));
                if ($colors['alpha'] > $alpha)
                {
                    $alpha = $colors['alpha'];
                }
                $newColor = imagecolorallocatealpha($image, $colors['red'], $colors['green'], $colors['blue'], $alpha);
                imagesetpixel($image, $x, ($height + ($height - $y) + $this->gradientDistance) - 1, $newColor);
            }
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
        $width = $imageData->getImageWidth();
        $height = $imageData->getImageHeight();

        if ($this->reflectionHeight > $height)
        {
            $this->reflectionHeight = $height;
        }

        if (empty($this->reflectionBackgroundColor))
        {
            $this->reflectionBackgroundColor = 'transparent';
        }

        /** @var \Imagick */
        $reflection = clone $imageData;
        $reflection->flipImage();

        $gradient = new \Imagick();
        $gradient->newPseudoImage($reflection->getImageWidth(), $this->reflectionHeight, 'gradient:transparent-white');

        $reflection->compositeImage($gradient, \Imagick::COMPOSITE_OVER, 0, 0);
        $reflection->setImageOpacity(1);

        $canvas = new \Imagick();
        $newHeight = $height + $this->reflectionHeight + $this->gradientDistance;
        $canvas->newImage($width, $newHeight, $this->reflectionBackgroundColor, 'png');
        $canvas->compositeImage($imageData, \Imagick::COMPOSITE_OVER, 0, 0);
        $canvas->compositeImage($reflection, \Imagick::COMPOSITE_OVER, 0, $height + $this->gradientDistance);

        return $canvas;
    }
}
