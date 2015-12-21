<?php
/**
* JPEGRenderer.class.php
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


namespace System\Output\Renderer;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Outputs the given image object as a JPG image.
* @package \System\Output\Renderer
*/
class JPEGRenderer extends \System\Output\Renderer
{
    /**
    * Outputs the given image object as a JPG image. The output of this renderer can be written to any RenderSurface.
    * @param \System\Image\Image The image to render as a JPG image.
    * @param int The quality used. Default is 75. Range is 0..100
    */
    public final function render()
    {
        $args = func_get_args();

        if (((count($args) != 1) && (count($args) != 2)) ||
            (!($args[0] instanceof \System\Image\Image)))
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $output = '';
        $quality = 75;

        if (count($args) == 2)
        {
            $quality = $args[1];
        }

        ob_start();
        imagejpeg($args[0]->getImageData(), null, $quality);
        $output = ob_get_contents();
        ob_end_clean();

        $this->addToBuffer($output);
    }

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public final function getHeaderSuggestions()
    {
        return new \System\Collection\Vector('Content-Type: image/jpeg');
    }
}
