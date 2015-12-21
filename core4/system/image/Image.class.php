<?php
/**
* Image.class.php
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
* The base image class for image representations.
* This base class implements functionality using the default GD implementation.
* @package \System\Image
*/
class Image extends \System\Base\Base
{
    /**
    * @var \System\Image\GD\Image The default handle to an image manipulation object
    */
    private $gdObject = null;

    /**
    * Creates an Image object from a filename
    * @param string The filename of the image
    */
    public function __construct_1($filename)
    {
        $this->gdObject = new \System\Image\GD\Image($filename);
    }

    /**
    * Creates an Image object from scratch
    * @param int The width of the image
    * @param int The height of the image
    */
    public function __construct_2($width, $height)
    {
        $this->gdObject = new \System\Image\GD\Image($width, $height);
    }

	/**
    * Creates an Image object from scratch
    * @param int The width of the image
    * @param int The height of the image
    * @param int The red component
    * @param int The green component
    * @param int The blue component
    */
    public function __construct_5($width, $height, $r, $g, $b)
    {
    	$this->gdObject = new \System\Image\GD\Image($width, $height, $r, $g, $b);
	}

    /**
    * Returns the data for the application of the effect.
    * This may be in a format specific to the type of class. The effects
    * should implement a type check for the specific image type class.
    * @return mixed The data for the effects to operate on
    */
    protected function getImageDataForEffect()
    {
        return $this->gdObject->getImageDataForEffect();
    }

    /**
    * Sets the new image data after an effect has been applied
    * @param mixed The new imagedata to be applied.
    */
    protected function setImageDataForEffect($imageData)
    {
        $this->gdObject->setImageDataForEffect($imageData);
    }

    /**
    * Applies an image effect to the current image.
    * @param \System\Image\ImageEffect The effect to apply
    */
    public final function applyEffect(\System\Image\Effect\ImageEffect $effect)
    {
        $imageData = $this->getImageDataForEffect();
        $this->setImageDataForEffect($effect->executeFilter($this, $imageData));
    }

    /**
    * Rescales the current image to the given bounding box.
    * This function allows for upscaling and.
    * This function rescales proportional.
    * @param int The maximum amount of pixels for the targetboundary
    */
    public final function rescaleProportional($boundary = 900)
    {
        $this->rescaleProportionalWH($boundary, $boundary);
    }

    /**
    * Rescales the current image to the given dimensions.
    * Rescales proportional and allows for upscaling.
    * @param int The new maximum width
    * @param int The new maximum height
    */
    public function rescaleProportionalWH($width = 900, $height = 900)
    {
        $this->gdObject->rescaleProportionalWH($width, $height);
    }

    /**
    * Adds a watermark to the image. This function can be called multiple times to add multiple watermark to the image.
    * When the image is smaller than the watermark, no watermark will be added.
    * @param string The watermark filename
    * @param int The position of the watermark
    */
    public function setImageWatermark($watermarkFile, $position = \System\Image\WatermarkPosition::POSITION_BOTTOMRIGHT)
    {
        $this->gdObject->setImageWatermark($watermarkFile, $position);
    }

    /**
    * Crops a part of the current image and rescales the image to a given format.
    * @param int The X position of the image to start cropping
    * @param int The Y position of the image to start cropping
    * @param int The width of the cropping space
    * @param int The height of the cropping space
    * @param int The width of the new image
    * @param int The height of the new image
    */
    public function cropImage($startX = 0, $startY = 0, $sourceWidth = 300, $sourceHeight = 300, $destinationWidth = 150, $destinationHeight = 150)
    {
        $this->gdObject->cropImage($startX, $startY, $sourceWidth, $sourceHeight, $destinationWidth, $destinationHeight);
    }

    /**
    * Rescales the current image to new dimensions.
    * This function does not rescale proportional.
    * @param int The new width of the image
    * @param int The new height of the image
    */
    public function rescale($width = 900, $height = 900)
    {
        $this->gdObject->rescale($width, $height);
    }

    /**
    * Turns the image to the left
    */
    public final function rotateCounterClockwise()
    {
        $this->turnByDegrees(90);
    }

    /**
    * Turns the image to the right
    */
    public final function rotateClockwise()
    {
        $this->turnByDegrees(270);
    }

    /**
    * Turns the image by the given amount of degrees.
    * If the hex color is an empty string, then a transparent backgroundcolor is used.
    * The rotation uses a counterclockwise offset.
    * @param integer The amount of degrees to turn
    * @param string The hex color used for the background.
    */
    public function rotateByDegrees($degrees, $backgroundColor = '')
    {
        $this->gdObject->rotateByDegrees($degrees, $backgroundColor);
    }

    /**
    * Gets the width of the image
    * @return integer The width of the image
    */
    public function getWidth()
    {
        return $this->gdObject->getWidth();
    }

    /**
    * Gets the height of the image
    * @return integer The height of the image
    */
    public function getHeight()
    {
        return $this->gdObject->getHeight();
    }

    /**
    * Returns the raw GD resource data.
    * @return resource The raw image data
    */
    public function getImageData()
    {
        return $this->gdObject->getImageData();
    }

    /**
    * Wrties the current image to a file using default settings.
    * When more advanced saving options are required, you should use the getImageData() function.
    * Returns a File object for easy file handling.
    * @param string The filename for the image. This should be a full filename.
    * @param int The imageType to use.
    * @param bool True to use interlacing (set to True for progressive JPEG)
    * @return \System\IO\File The file object
    */
    public function writeToFile($filename, $imageType = \System\Image\ImageType::TYPE_JPEG, $interlaced = false)
    {
        return $this->gdObject->writeToFile($filename, $imageType, $interlaced);
    }
}
