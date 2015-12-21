<?php
/**
* Queue.class.php
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
* Basic Queue implementation, based on Vector collections.
* @package \System\Collection
*/
class Queue extends \System\Collection\Vector implements iQueue
{
    /**
    * Takes the first item from the Queue and delete that from
    * the Queue.
    * @return mixed Returns the first element from the Queue
    * @package \System\Collection
    */
    public function take()
    {
        $v = null;

        if ($this->count() > 0)
        {
            $k = 0;

            //we iterate only to get the first value, then we stop.
            //We do this because there is no begin() equivalent.
            foreach ($this as $index=>$value)
            {
                $k = $index;
                $v = $value;
                break;
            }
            unset($this[$k]);
        }
        return $v;
    }

    /**
    * Peeks at the queue, returning its value but
    * keeping the queue intact.
    * @return mixed The peeked item from the queue
    */
    public function peek()
    {
    	$v = null;

        if ($this->count() > 0)
        {
            $k = 0;

            //we iterate only to get the first value, then we stop.
            //We do this because there is no begin() equivalent.
            foreach ($this as $index=>$value)
            {
                $k = $index;
                $v = $value;
                break;
            }
        }
        return $v;
	}

	/**
    * Adds a value to the bottom of the Vector
    * @param mixed The value to add
    * @return int the index of the newly created item
    */
    public function add($value)
    {
        $this[] = $value;

        return $this->count();
    }
}