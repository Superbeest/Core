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


namespace System\Db\Queue;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the queue for a database table
* @package \System\Db\Queue
*/
class Queue extends \System\Base\BaseObj implements \System\Collection\iQueue, \Countable
{
	/**
	* @publicget
	* @var string The name of the queue table
	*/
	protected $queueTableName = null;

	/**
	* @var \System\Db\Database The database to work with.
	*/
	protected $db = null;

	public final function __construct(\System\Db\Database $db, $queueTableName)
	{
		$this->queueTableName = $queueTableName;
		$this->db = $db;
	}

	/**
    * Adds a value to the bottom of the Queue
    * @param mixed The value to add
	* @return int the index of the newly created item
    */
	public function add($value)
	{
		$val = serialize($value);
		$query = new \System\Db\Query($this->db, SQL_QUEUE_ADD);
		$query->bind($this->queueTableName, \System\Db\QueryType::TYPE_QUERY);
		$query->bind($val, \System\Db\QueryType::TYPE_STRING);

		$this->db->query($query);

		return $this->db->getInsertId();
	}

	/**
    * Takes the first item from the Queue and delete that from
    * the Queue.
    * @return mixed Returns the first element from the Queue
    */
	public function take()
	{
		$value = null;

		$query = new \System\Db\Query($this->db, SQL_QUEUE_PEEK);
		$query->bind($this->queueTableName, \System\Db\QueryType::TYPE_QUERY);

		$result = $this->db->querySingle($query);
		if ($result)
		{
			$value = unserialize($result->value);

			$query = new \System\Db\Query($this->db, SQL_QUEUE_DELETE);
			$query->bind($this->queueTableName, \System\Db\QueryType::TYPE_QUERY);
			$query->bind($result->id, \System\Db\QueryType::TYPE_INTEGER);
			$this->db->query($query);
		}

		return $value;
	}

	/**
	* Returns the amount of items in the queue
	* @return int The amount of items in the queue
	*/
	public function count()
	{
		$query = new \System\Db\Query($this->db, SQL_QUEUE_COUNT);
		$query->bind($this->queueTableName, \System\Db\QueryType::TYPE_QUERY);

		return $this->db->querySingle($query)->amount;
	}

	/**
    * Peeks at the queue, returning its value but
    * keeping the queue intact.
    * @return mixed The peeked item from the queue
    */
    public function peek()
    {
    	$query = new \System\Db\Query($this->db, SQL_QUEUE_PEEK);
		$query->bind($this->queueTableName, \System\Db\QueryType::TYPE_QUERY);

		$result = $this->db->querySingle($query);
		if ($result)
		{
			return unserialize($result->value);
		}

		return null;
	}
}
