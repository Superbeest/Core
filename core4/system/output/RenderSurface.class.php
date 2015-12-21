<?php
/**
* RenderSurface.class.php
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


namespace System\Output;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The RenderSurface base class. This class serves as the parent for all the render surfaces
* @package \System\Output
*/
abstract class RenderSurface extends \System\Base\SingletonBase
{
    /**
    * @var \System\Output\Renderer The renderer to process
    */
    private $renderer = null;

    /**
    * @var \System\Collection\Vector The headers to output
    */
    private $headers = null;

    /**
    * Returns an initialized RenderSurface. The surface is created using a factory pattern structure.
    * @param string The full classname of the rendersurface to create
    * @return \System\Output\RenderSurface The RenderSurface to write the buffer to
    */
    public static final function getSurface($surfaceClass)
    {
        if ((class_exists($surfaceClass)) &&
            (in_array(get_called_class(), class_parents($surfaceClass))))
        {
            return $surfaceClass::getInstance();
        }
    }

    /**
    * Returns the current renderer
    * @return \System\Output\Renderer The current renderer, or null on no renderer
    */
    protected final function getRenderer()
    {
        return $this->renderer;
    }

    /**
    * Sets a renderer to process. This renderer will be placed on the surface.
    * @param \System\Output\Renderer The renderer to process.
    */
    public final function setRenderer(\System\Output\Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
    * Every RenderSurface should implement this function as it outputs the Renderer.
    * @param \System\Output\Renderer The renderer
    */
    protected abstract function render(\System\Output\Renderer $renderer);

    /**
    * Sets the headers for the RenderSurface
    * @param \System\Collection\Vector The headers directives
    */
    public final function setHeaders(\System\Collection\Vector $headers)
    {
        $this->headers = $headers;
    }

    /**
    * Return the currently set header directives
    * @return \System\Collection\Vector The currently set headers
    */
    public final function getHeaders()
    {
        if (!$this->headers)
        {
            $this->headers = new \System\Collection\Vector();
        }

        return $this->headers;
    }

    /**
    * Adds a single header directive to the RenderSurface
    * @param string The headerdirective to add
    */
    public final function addHeader($directive)
    {
        $headers = $this->getHeaders();
        $headers[] = $directive;
        $this->setHeaders($headers);
    }

    /**
    * Outputs all the given headers and retrieves the header suggestions from the renderer.
    * Specific RenderSurfaces may override this function so to decide what to do with the headers.
    */
    protected function outputHeaders(\System\Output\Renderer $renderer)
    {
        $headers = $this->getHeaders();
        $headers->combine($renderer->getHeaderSuggestions());

        foreach ($headers as $header)
        {
            header($header);
        }
    }

    /**
    * Starts the rendering of the current RenderSurface. This validates the Renderer
    * and executes the render function.
    */
    public final function execute()
    {
        $renderer = $this->getRenderer();
        if ($renderer)
        {
            $this->outputHeaders($renderer);
            $this->render($renderer);
        }
        else
        {
            throw new \Exception('No Renderer given to the current RenderSurface. Please add a Renderer to the RenderSurface');
        }
    }
}
