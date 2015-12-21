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


namespace System\Image\Imagick;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The base image class for image representations
* @package \System\Image\Imagick
*/
class Image extends \System\Image\Image
{
    /**
    * The export format used
    */
    const OUTPUT_FORMAT = 'png';

    /**
    * @var \Imagick The Imagick object
    */
    protected $image = null;

    /**
    * Creates an Image object from a filename
    * @param string The filename of the image
    */
    public final function __construct_1($filename)
    {
        if (!file_exists($filename))
        {
            throw new \System\Error\Exception\FileNotFoundException('File ' . $filename . ' was not found');
        }

        $this->image = new \Imagick($filename);
        $this->image->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
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
        switch ($imageType)
        {
            case \System\Image\ImageType::TYPE_JPEG:
            	if ($interlaced)
            	{
            		$this->image->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
				}
                $this->image->setImageFormat('jpeg');
                break;
            case \System\Image\ImageType::TYPE_GIF:
                $this->image->setImageFormat('gif');
                break;
            case \System\Image\ImageType::TYPE_PNG:
                $this->image->setImageFormat('png');
                break;
            default:
                throw new \InvalidArgumentException('Unknown imageType given: ' . $imageType);
        }

        $this->image->writeImage($filename);
        return new \System\IO\File($filename);
    }

    /**
    * Creates an Image object from scratch
    * @param int The width of the image
    * @param int The height of the image
    */
    public final function __construct_2($width, $height)
    {
        $this->__construct_3($width, $height, 'black');
    }

    /**
    * Creates an Image object from scratch
    * @param int The width of the image
    * @param int The height of the image
    * @param string The color to use as the background, it can be 'transparent'
    */
    public final function __construct_3($width, $height, $backgroundColor)
    {
    	$this->image = new \Imagick();
        $this->image->newImage($width, $height, new \ImagickPixel($backgroundColor));
	}

    /**
    * Returns the data for the application of the effect.
    * This may be in a format specific to the type of class. The effects
    * should implement a type check for the specific image type class.
    * @return mixed The data for the effects to operate on
    */
    protected final function getImageDataForEffect()
    {
        return $this->image;
    }

    /**
    * Sets the new image data after an effect has been applied
    * @param mixed The new imagedata to be applied.
    */
    protected final function setImageDataForEffect($imageData)
    {
        $this->image = $imageData;
    }

    /**
    * Rescales the current image to the given dimensions.
    * Rescales proportional and allows for upscaling.
    * @param int The new maximum width
    * @param int The new maximum height
    */
    public final function rescaleProportionalWH($width = 900, $height = 900)
    {
        $this->image->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
    }

    /**
    * Adds a watermark to the image. This function can be called multiple times to add multiple watermark to the image.
    * When the image is smaller than the watermark, no watermark will be added.
    * @param string The watermark filename
    * @param int The position of the watermark
    */
    public final function setImageWatermark($watermarkFile, $position = \System\Image\WatermarkPosition::POSITION_BOTTOMRIGHT)
    {
        if (!file_exists($watermarkFile))
        {
            throw new \System\Error\Exception\FileNotFoundException('File ' . $watermarkFile . ' was not found');
        }

        $watermarkImage = new \Imagick($watermarkFile);

        $origSX = $this->image->getImageWidth();
        $origSY = $this->image->getImageHeight();

        $waterSX = $watermarkImage->getImageWidth();
        $waterSY = $watermarkImage->getImageHeight();

        if (($waterSX > $origSX) ||
            ($waterSY > $origSY))
        {
            //we dont need to add watermarks to images smaller than the watermark itself.
            return;
        }

        switch ($position)
        {
            case \System\Image\WatermarkPosition::POSITION_BOTTOMLEFT:
                $destX = 0;
                $destY = $origSY - $waterSY;
                break;
            case \System\Image\WatermarkPosition::POSITION_TOPLEFT:
                $destX = 0;
                $destY = 0;
                break;
            case \System\Image\WatermarkPosition::POSITION_TOPRIGHT:
                $destX = $origSX - $waterSX;
                $destY = 0;
                break;
            case \System\Image\WatermarkPosition::POSITION_CENTER:
                $destX = ($origSX / 2) - ($waterSX / 2);
                $destY = ($origSY / 2) - ($waterSY / 2);
                break;
            case \System\Image\WatermarkPosition::POSITION_BOTTOMRIGHT:
            default:
                $destX = $origSX - $waterSX;
                $destY = $origSY - $waterSY;
                break;
        }

        $this->image->compositeImage($watermarkImage, \Imagick::COMPOSITE_OVER, $destX, $destY);
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
    public final function cropImage($startX = 0, $startY = 0, $sourceWidth = 300, $sourceHeight = 300, $destinationWidth = 150, $destinationHeight = 150)
    {
        $this->image->cropImage($sourceWidth, $sourceHeight, $startX, $startY);
        $this->rescale($destinationWidth, $destinationHeight);
    }

    /**
    * Rescales the current image to new dimensions.
    * This function does not rescale proportional.
    * @param int The new width of the image
    * @param int The new height of the image
    */
    public final function rescale($width = 900, $height = 900)
    {
        $this->image->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, false);
    }

    /**
    * Turns the image by the given amount of degrees.
    * If the hex color is an empty string, then a transparent backgroundcolor is used.
    * The rotation uses a counterclockwise offset.
    * @param integer The amount of degrees to turn
    * @param string The hex color used for the background.
    */
    public final function rotateByDegrees($degrees, $backgroundColor = '')
    {
        $r = 0;
        $g = 0;
        $b = 0;
        if (!empty($backgroundColor))
        {
            \System\Image\ColorConversion::hexToRGB($backgroundColor, $r, $g, $b);
        }

        $this->image->rotateImage(new \ImagickPixel('rgb(' . $r . ',' . $g . ',' . $b . ')'), -$degrees);
    }

    /**
    * Gets the width of the image
    * @return integer The width of the image
    */
    public final function getWidth()
    {
        return $this->image->getImageWidth();
    }

    /**
    * Gets the height of the image
    * @return integer The height of the image
    */
    public final function getHeight()
    {
        return $this->image->getImageHeight();
    }

    /**
    * Returns the raw GD resource data.
    * @param bool True for the use of a temporary swap file. This can be skipped, but might lead to out of memory errors.
    * @return resource The raw image data
    */
    public final function getImageData($useTemporaryFile = true)
    {
        $this->image->setImageFormat(self::OUTPUT_FORMAT);
        if ($useTemporaryFile)
        {
            $image = $this->getImage();
            return $image->getImageData();
        }
        else
        {
            return imagecreatefromstring($this->image->getImageBlob());
        }
    }

	/**
	* Get the raw Imagick resource data
	* @return \Imagick The raw image data
	*/
    public final function getImageDataImagick()
    {
    	return $this->image;
	}

    /**
    * Returns a GD converted variant of the image.
    * This uses a temporary file to swap data from the imagick library to the gd library.
    * This is done to ensure we can assign enough memory using our GD\Image class.
    * @see getImageData()
    * @return \System\Image\GD\Image The GD Image object
    */
    public final function getImage()
    {
        $this->image->setImageFormat(self::OUTPUT_FORMAT);
        $tmpFolder = sys_get_temp_dir();

        if ($filename = tempnam($tmpFolder, 'imagick_gd_conv'))
        {
            $this->image->writeImage($filename);
            $image = new \System\Image\GD\Image($filename);
            unlink($filename);
            return $image;
        }

        throw new \System\Error\Exception\FileNotFoundException('Could not generate a file handle for a temporary swap file');
    }
}
