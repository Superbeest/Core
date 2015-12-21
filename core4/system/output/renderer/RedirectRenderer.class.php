<?php
/**
* RedirectRenderer.class.php
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
* Redirects the output to another URL
* @package \System\Output\Renderer
*/
class RedirectRenderer extends \System\Output\Renderer
{
    /**
    * @publicget
    * @publicset
    * @var string The url to redirect to.
    */
    protected $url = '';

    /**
    * Redirects to the given parameter URL
    * @param string The URL to redirect to
    */
    public final function render()
    {
        $args = func_get_args();

        if (count($args) != 1)
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $this->url = $args[0];
    }

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public final function getHeaderSuggestions()
    {
        return new \System\Collection\Vector('Location: ' . $this->url);
    }
}