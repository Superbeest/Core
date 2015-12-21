<?php
/**
* iQueue.interface.php
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
* Interface for queues.
* @package \System\Collection
*/
interface iQueue extends \System\Base\iBaseObj
{
	/**
    * Adds a value to the bottom of the Queue
    * @param mixed The value to add
    * @return int the index of the newly created item
    */
	public function add($value);

	/**
    * Takes the first item from the Queue and delete that from
    * the Queue.
    * @return mixed Returns the first element from the Queue
    */
	public function take();

	/**
    * Peeks at the queue, returning its value but
    * keeping the queue intact.
    * @return mixed The peeked item from the queue
    */
    public function peek();
}