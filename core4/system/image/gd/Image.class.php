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


namespace System\Image\GD;

if (!defined('System'))
{
    die ('Hacking attempt');
}

//when we start working with images, we usually need more memory than the default. also we do accept jpeg inconsistencies.
@ini_set("gd.jpeg_ignore_warning", true);

/**
* The base image class for image representations
* @package \System\Image\GD
*/
class Image extends \System\Image\Image
{
    /**
    * The maximum amount of memory for the system
    */
    const MEMORY_LIMIT_MAX = '256M';

    /**
    * @var resource The raw imagedata
    */
    protected $image;

    /**
    * Creates an Image object from a filename
    * @param string The filename of the image
    */
    public final function __construct_1($filename)
    {
        $this->image = $this->getImageObject($filename);
    }

    /**
    * Creates an Image object from scratch
    * @param int The width of the image
    * @param int The height of the image
    */
    public final function __construct_2($width, $height)
    {
        self::__construct_5($width, $height, 0, 0, 0);
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
    	$this->image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($this->image, $r, $g, $b);
        imagefill($this->image, 0, 0, $color);
	}

	/**
	* Creates a new image object from the given GD resource
	* @param resource The GD image resource
	* @return Image
	*/
	public static function createFromGD($imageData)
	{
		$x = imagesx($imageData);
		$y = imagesy($imageData);

		$image = new Image($x, $y);
		$image->image = $imageData;

		return $image;
	}

    private static final function setMemoryRequirements($imageInformation)
    {
        //width * height * bitsPerColor * amountOfColors = total amount of bits for the image / \System\Math\Math::BITS_PER_BYTE = #required bytes
        $bits = isset($imageInformation['bits']) ? $imageInformation['bits'] : 8; //8 is default
        $channels = isset($imageInformation['channels']) ? $imageInformation['channels'] : 4; //4 channels to make sure
        $requiredMemory = ($imageInformation[0] * $imageInformation[1] * $bits * $channels) / \System\Math\Math::BITS_PER_BYTE;

        //apply the overhead fudge factor
        $requiredMemory = (int)round($requiredMemory + pow(2, 16)) * 1.65;

        //add the system requirements to it
        $requiredMemory += memory_get_usage();

        // the current limit multiplied to bytes
        $currentLimit = (int)ini_get('memory_limit') * \System\HTTP\Storage\FileSizes::FILESIZE_1M;

        if ($requiredMemory > $currentLimit)
        {
            $newMemoryValue = ceil(($requiredMemory + $currentLimit) / \System\HTTP\Storage\FileSizes::FILESIZE_1M) . 'M';
            if ($newMemoryValue <= self::MEMORY_LIMIT_MAX)
            {
                ini_set('memory_limit', $newMemoryValue);
            }
        }
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
            		imageinterlace($this->image, true);
				}
                imagejpeg($this->image, $filename);
                break;
            case \System\Image\ImageType::TYPE_GIF:
                imagegif($this->image, $filename);
                break;
            case \System\Image\ImageType::TYPE_PNG:
                imagepng($this->image, $filename);
                break;
            default:
                throw new \InvalidArgumentException('Unknown imageType given: ' . $imageType);
        }

        return new \System\IO\File($filename);
    }

    /**
    * Creates the resource object from the given filename
    * @param string The filename of the image to load
    * @return resource The loaded image resource
    */
    private final function getImageObject($filename)
    {
        if (!file_exists($filename))
        {
            throw new \System\Error\Exception\FileNotFoundException('File ' . $filename . ' was not found');
        }

        $imageInformation = getimagesize($filename);
        static::setMemoryRequirements($imageInformation);

        $image = null;

        switch ($imageInformation['mime'])
        {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filename);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filename);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filename);
                imagesavealpha($image, true);

                break;
            case 'image/bmp':
                $image = $this->imagecreatefrombmp($filename);
                break;
            default:
                $image = null;
                break;
        }

        if (!$image)
        {
            throw new \System\Error\Exception\SystemException('Could not load the given image: ' . $filename);
        }

        return $image;
    }

    /**
    * Returns the data for the application of the effect.
    * This may be in a format specific to the type of class. The effects
    * should implement a type check for the specific image type class.
    * @return mixed The data for the effects to operate on
    */
    protected final function getImageDataForEffect()
    {
        return $this->getImageData();
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
    * @param bool Allows increasing the size of the image above its current size. Will make it blocky.
    */
    public final function rescaleProportionalWH($width = 900, $height = 900, $allowBlowUp = false)
    {
        $sizeX = imagesx($this->image);
        $sizeY = imagesy($this->image);

        $ratio = $sizeX / $sizeY;

        $newWidth = $width;
        $newHeight = $height;

        if ((($newWidth < $sizeX) || $allowBlowUp) ||
            (($newHeight < $sizeY) || $allowBlowUp))
        {
            if (($newWidth / $newHeight) > $ratio)
            {
                $newWidth = $newHeight * $ratio;
            }
            else
            {
                $newHeight = $newWidth  / $ratio;
            }

            $this->doRescale($newWidth, $newHeight, $sizeX, $sizeY);
        }
    }

    /**
    * Does the rescaling of the current image object to the new given dimensions.
    * The sizeX and sizeY parameters are passed for speed increase.
    * @param int The new width
    * @param int The new height
    * @param int The width of the current image
    * @param int The height of the current image
    */
    private function doRescale($newWidth, $newHeight, $sizeX, $sizeY)
    {
    	$newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $sizeX, $sizeY);

        imagedestroy($this->image);
        $this->image = $newImage;
	}

	/**
	* Rescales the current image proportionally to the given width. Optional upscaling is possible.
	* @param int The desired with
	* @param bool Allow the image to be upscaled
	*/
    public function rescaleProportionalToW($width = 900, $allowBlowUp = false)
    {
    	$sizeX = imagesx($this->image);
        $sizeY = imagesy($this->image);

        $ratio = $sizeX / $sizeY;

        $newWidth = $width;

        if (($newWidth < $sizeX) ||
        	($allowBlowUp))
        {
        	$newHeight = $newWidth / $ratio;

        	$this->doRescale($newWidth, $newHeight, $sizeX, $sizeY);
		}
	}

	/**
	* Rescales the current image proportionally to the given height. Optional upscaling is possible.
	* @param int The desired height
	* @param bool Allow the image to be upscaled
	*/
	public function rescaleProportionalToH($height = 900, $allowBlowUp = false)
	{
		$sizeX = imagesx($this->image);
        $sizeY = imagesy($this->image);

        $ratio = $sizeX / $sizeY;

        $newHeight = $height;

        if (($newHeight < $sizeY) ||
        	($allowBlowUp))
        {
        	$newWidth = $newHeight / $ratio;

        	$this->doRescale($newWidth, $newHeight, $sizeX, $sizeY);
		}
	}

    /**
    * Adds a watermark to the image. This function can be called multiple times to add multiple watermark to the image.
    * When the image is smaller than the watermark, no watermark will be added.
    * @param string The watermark filename
    * @param int The position of the watermark
    */
    public final function setImageWatermark($watermarkFile, $position = \System\Image\WatermarkPosition::POSITION_BOTTOMRIGHT)
    {
        $watermarkImage = $this->getImageObject($watermarkFile);

        $origSX = imagesx($this->image);
        $origSY = imagesy($this->image);

        $waterSX = imagesx($watermarkImage);
        $waterSY = imagesy($watermarkImage);

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

        imagecopy($this->image, $watermarkImage, $destX, $destY, 0, 0,$waterSX, $waterSY);
        imagedestroy($watermarkImage);
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
        $newImage = imagecreatetruecolor($destinationWidth, $destinationHeight);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        imagecopyresampled($newImage, $this->image, 0, 0, $startX, $startY, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);

        imagedestroy($this->image);
        $this->image = $newImage;
    }

    /**
    * Rescales the current image to new dimensions.
    * This function does not rescale proportional.
    * @param int The new width of the image
    * @param int The new height of the image
    * @param bool Allows increasing the size of the image above its current size. Will make it blocky.
    */
    public final function rescale($width = 900, $height = 900, $allowBlowUp = false)
    {
        $sizeX = imagesx($this->image);
        $sizeY = imagesy($this->image);

        $newWidth = (($width < $sizeX) || $allowBlowUp) ? $width : $sizeX;
        $newHeight = (($height < $sizeY) || $allowBlowUp) ? $height : $sizeY;

        $this->doRescale($newWidth, $newHeight, $sizeX, $sizeY);
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
        $color = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
        if (!empty($backgroundColor))
        {
            $r = 0;
            $g = 0;
            $b = 0;
            if (\System\Image\ColorConversion::hexToRGB($backgroundColor, $r, $g, $b))
            {
                $color = imagecolorallocate($this->image, $r, $g, $b);
            }
        }

        $this->image = imagerotate($this->image, $degrees, $color);
        imagesavealpha($this->image, true);
    }

    /**
    * Loads the image from a BMP file.
    * @param string The filename
    * @return resource A resource with the imagedata
    */
    protected final function imagecreatefrombmp($filename)
    {
        if (!$f1 = fopen($filename, "rb"))
        {
            return false;
        }

        $file = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
        if ($file['file_type'] != 19778)
        {
            return false;
        }

        $bmp = unpack(
            'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));

        $bmp['colors'] = pow(2, $bmp['bits_per_pixel']);

        if ($bmp['size_bitmap'] == 0)
        {
            $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        }

        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel'] / 8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] = ($bmp['width'] * $bmp['bytes_per_pixel'] / 4);
        $bmp['decal'] -= floor($bmp['width'] * $bmp['bytes_per_pixel'] / 4);
        $bmp['decal'] = 4 - (4 * $bmp['decal']);
        if ($bmp['decal'] == 4)
        {
            $bmp['decal'] = 0;
        }

        $palette = array();
        if ($bmp['colors'] < 16777216)
        {
            $palette = unpack('V' . $bmp['colors'], fread($f1, $bmp['colors'] * 4));
        }

        $img = fread($f1, $bmp['size_bitmap']);
        $vide = chr(0);

        $res = imagecreatetruecolor($bmp['width'], $bmp['height']);
        $p = 0;
        $y = $bmp['height'] - 1;

        while ($y >= 0)
        {
            $x=0;
            while ($x < $bmp['width'])
            {
                switch ($bmp['bits_per_pixel'])
                {
                    case 24:
                        $color = unpack("V", substr($img, $p, 3) . $vide);
                        break;
                    case 16:
                        $color = unpack("v", substr($img, $p, 2));
                        $blue  = (($color[1] & 0x001f) << 3) + 7;
                        $green = (($color[1] & 0x03e0) >> 2) + 7;
                        $red   = (($color[1] & 0xfc00) >> 7) + 7;
                        $color[1] = $red * 65536 + $green * 256 + $blue;
                        break;
                    case 8:
                        $color = unpack("n", $vide . substr($img, $p, 1));
                        $color[1] = $palette[$color[1] + 1];
                        break;
                    case 4:
                        $color = unpack("n", $vide . substr($img, floor($p), 1));
                        if (($p * 2) % 2 == 0)
                        {
                            $color[1] = ($color[1] >> 4);
                        }
                        else
                        {
                            $color[1] = ($color[1] & 0x0F);
                        }
                        $color[1] = $palette[$color[1]+1];
                        break;
                    case 1:
                        $color = unpack("n", $vide . substr($img, floor($p), 1));
                        switch (($p * 8) % 8)
                        {
                            case 0:
                                $color[1] = $color[1] >> 7;
                                break;
                            case 1:
                                $color[1] = ($color[1] & 0x40) >> 6;
                                break;
                            case 2:
                                $color[1] = ($color[1] & 0x20) >> 5;
                                break;
                            case 3:
                                $color[1] = ($color[1] & 0x10) >> 4;
                                break;
                            case 4:
                                $color[1] = ($color[1] & 0x8) >> 3;
                                break;
                            case 5:
                                $color[1] = ($color[1] & 0x4) >> 2;
                                break;
                            case 6:
                                $color[1] = ($color[1] & 0x2) >> 1;
                                break;
                            case 7:
                                $color[1] = ($color[1] & 0x1);
                                break;
                            default:
                                throw new \Exception("Image imagecreatefrombmp invalid bpp value!");
                        }
                        $color[1] = $palette[$color[1] + 1];
                        break;
                    default:
                        return false;
                }
                imagesetpixel($res, $x, $y, $color[1]);
                $x++;
                $p += $bmp['bytes_per_pixel'];
            }
            $y--;
            $p += $bmp['decal'];
        }

        fclose($f1);

        return $res;
    }

    /**
    * Gets the width of the image
    * @return integer The width of the image
    */
    public final function getWidth()
    {
        return imagesx($this->image);
    }

    /**
    * Gets the height of the image
    * @return integer The height of the image
    */
    public final function getHeight()
    {
        return imagesy($this->image);
    }

    /**
    * Returns the raw GD resource data.
    * @return resource The raw image data
    */
    public final function getImageData()
    {
        return $this->image;
    }
}
