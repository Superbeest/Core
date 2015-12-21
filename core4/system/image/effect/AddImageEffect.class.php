<?php
/**
* AddImageEffect.class.php
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
* Applies an image to the current image at the given coordinates
* @package \System\Image\Effect
*/
class AddImageEffect extends \System\Image\Effect\ImageEffect
{
	/**
	* @var \System\Image\Image The Image to add
	*/
	private $imageToAdd = null;

	/**
	* @var int The X coordinate
	*/
	private $x = 0;

	/**
	* @var int The Y coordinate
	*/
	private $y = 0;

	/**
	* Applies an image to the current image at the given coordinates
	* @param \System\Image\Image The image object to add
	* @param int Destination X coordinate
	* @param int Destination Y coordinate
	*/
	public function __construct(\System\Image\Image $imageToAdd, $x, $y)
	{
		$this->imageToAdd = $imageToAdd;
		$this->x = $x;
		$this->y = $y;
	}

	/**
    * Applies the image effect on a GD resource. This function should be overridden by the effect.
    * @param resource The image resource to work with
    * @return resource The resource to use
    */
    protected final function executeFilterGD($imageData)
    {
    	imagecopy($imageData, $this->imageToAdd->getImageData(), $this->x, $this->y, 0, 0, $this->imageToAdd->getWidth(), $this->imageToAdd->getHeight());
    	return $imageData;
	}

	/**
    * Applies the image effect on an Imagick object. This function should be overridden by the effect.
    * @param \Imagick The image object to work with
    * @return \Imagick The new image object to use
    */
    protected function executeFilterImagick(\Imagick $imageData)
    {
    	$imageData->compositeImage($this->imageToAdd->getImageDataImagick(), \Imagick::COMPOSITE_OVER, $this->x, $this->y);
    	return $imageData;
	}
}