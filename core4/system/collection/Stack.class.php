<?php
/**
* Stack.class.php
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


namespace System\Collection;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Basic Stack implementation based on Vectors.
* Values can be added to the bottom of the stack and can
* also be popped off the bottom of the stack
* @package \System\Collection
*/
class Stack extends \System\Collection\Vector implements iStack
{
    /**
    * Pushes a value to the bottom of the Stack.
    * @param mixed The value to add to the Stack
    * @package \System\Collection
    */
    public function push($value)
    {
        $this[] = $value;
    }

    /**
    * Pops a value from the bottom of the Stack;
    * returning the value and removing the returned value from the Stack.
    * Note, this function does note check against empty Stack
    * @return mixed The mixed value on the Stack
    */
    public function pop()
    {
        $value = null;
        if ($this->hasItems())
        {
            $value = $this->peek();
            unset($this[$this->count() - 1]);
        }
        return $value;
    }

    /**
    * Peeks at the end of the Stack, returning its value but
    * keeping the Stack intact.
    * @return mixed The last item on the Stack
    */
    public function peek()
    {
        if ($this->hasItems())
        {
            return $this[$this->count() - 1];
        }
        return null;
    }
}
