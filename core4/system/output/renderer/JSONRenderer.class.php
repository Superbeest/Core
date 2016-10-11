<?php
/**
* JSONRenderer.class.php
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
* This class provides functionality to output specific values and objects as JSON
* @package \System\Output\Renderer
*/
class JSONRenderer extends \System\Output\Renderer
{
    /**
    * Converts the given input to a JSON representation.
    * A bitmask can be used for JSON encoding:
    * Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_FORCE_OBJECT (see PHP manual)
    * @param mixed The variable to encode to a JSON representation
    * @param int The bitmask to use for encoding.
    */
    public final function render()
    {
        $args = func_get_args();

        if (count($args) != 2)
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        list($variable, $bitmask) = $args;

        $output = json_encode($variable, $bitmask);

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
        return new \System\Collection\Vector('Content-Type: application/json');
    }
}

