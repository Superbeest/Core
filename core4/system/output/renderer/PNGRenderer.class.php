<?php
/**
* PNGRenderer.class.php
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
* Outputs the given image object as a PNG image.
* @package \System\Output\Renderer
*/
class PNGRenderer extends \System\Output\Renderer
{
    /**
    * Outputs the given image object as a PNG image. The output of this renderer can be written to any RenderSurface.
    * The image will be rendered with no filters applied.
    * @param \System\Image\Image The image to render as a PNG image.
    * @param int The amount of compression used. Default is 0. Range is 0..9
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
        $compression = 0;

        if (count($args) == 2)
        {
            $compression = $args[1];
        }

        $val = new \System\Security\Validate();
        if ($val->isInt($compression, 'compressionlevel', 0, 9, true) != \System\Security\ValidateResult::VALIDATE_OK)
        {
            throw new \InvalidArgumentException('Second parameter should be between 0..9');
        }

        ob_start();
        imagepng($args[0]->getImageData(), null, $compression);
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
        return new \System\Collection\Vector('Content-Type: image/png');
    }
}
