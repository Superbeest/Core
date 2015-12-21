<?php
/**
* DataRenderer.class.php
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
* This class provides functionality to output plain text data
* @package \System\Output\Renderer
*/
class DataRenderer extends \System\Output\Renderer
{
    /**
    * Outputs the given data to the RenderSurface.
    * This function supports two types of data: a string and a \System\Collection\Vector or an array.
    * If the first parameter is a string, then the function supports only 1 parameter;
    * If the first parameter is a \System\Collection\Vector, or an array, then this function uses the second parameter
    * as a delimiter.
    * @param mixed A string, \System\Collection\Vector, or array.
    */
    public final function render()
    {
        $args = func_get_args();

        if (count($args) == 0)
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $output = '';

        switch (true)
        {
            case \System\Type::getType($args[0]) == \System\Type::TYPE_STRING:
                $output = $args[0];
                break;
            case \System\Type::getType($args[0]) == \System\Type::TYPE_ARRAY:
                if (count($args) == 2)
                {
                    $output = implode($args[1], $args[0]);
                }
                break;
            case \System\Type::getType($args[0]) == \System\Type::TYPE_OBJECT:
                if (($args[0] instanceof \System\Collection\Vector) &&
                    (count($args) == 2))
                {
                    $output = implode($args[1], $args[0]->getArrayCopy());
                }
                else
                {
                    throw new \InvalidArgumentException('Invalid amount of arguments given.');
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $this->addToBuffer($output);
    }
}

