<?php
/**
* Renderer.class.php
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
* The base class for the renderers
* @package \System\Output
*/
abstract class Renderer extends \System\Base\Base
{
    /**
    * @var string The buffer to output
    */
    private $buffer = '';

    /**
    * Concatenates the given data to the buffer.
    * @param mixed The data to add
    */
    protected final function addToBuffer($data)
    {
        $this->buffer .= $data;
    }

    /**
    * Flushed the internal buffer. The buffer is cleared after this call.
    * @return mixed The data in the current buffer
    */
    public final function flush()
    {
        $buffer = $this->buffer;
        $this->buffer = '';
        return $buffer;
    }

    /**
    * Each Renderer should implement this function. The parameters may be retrieved by using the
    * func_get_args() function. This function should write its output to the buffer using the
    * addToBuffer function.
    */
    public abstract function render();

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public function getHeaderSuggestions()
    {
        return new \System\Collection\Vector();
    }
}

