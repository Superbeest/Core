<?php
/**
* DatabaseResult.class.php
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


namespace System\Db\HTTP;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the database result over http tunnels
* @package \System\Db\HTTP
*/
class DatabaseResult extends \System\Db\DatabaseResult
{
	/**
    * @var \System\Collection\Vector The result of the query
    */
    protected $results;

	/**
	* @var \System\Collection\Vector The fields
	*/
    protected $fields;

	/**
	* Decodes the header and checks the values. Also returns optional errors
	* @param string The result to decode
	* @return int The error number. 0 indicates all is fine
	*/
	private function decodeHeader(&$result)
	{
		$arr = unpack("Nlong/nshort/NerrorNumber/c6dummy", $result);
		$result = substr($result, 16);

		assert($arr['long'] == 1111);
		assert($arr['short'] == 202);
		return $arr['errorNumber'];
	}

	/**
	* Decodes a string and returns it
	* @param string The result to decode
	* @return string The decoded string
	*/
	private function getString(&$result)
	{
		$arr = unpack("clen", $result);
		$result = substr($result, 1);
		if ($arr['len'] == -2) //the length header is larger than 254 chars. -2 for decoding in char
		{
			$arr = unpack('Nlen', $result);
			$result = substr($result, 4);
		}
		$strArr = unpack('c' . $arr['len'], $result);
		$result = substr($result, (int)$arr['len']);
		$str = '';
		foreach ($strArr as $c)
		{
			$str .= chr($c);
		}
		return $str;
	}

	/**
	* Gets the fieldtype for the given field int
	* @param int the type value
	* @return string The type
	*/
	public function getFieldType($type)
	{
		switch ((int)$type)
		{
			case 8:
			case 3:
				return 'int';
			case 4:
			case 5:
			case 0:
				return 'real';
			case 6:
				return 'null';
			case 7:
				return 'timestamp';
			case 10:
				return 'date';
			case 11:
				return 'time';
			case 12:
				return 'datetime';
			case 13:
				return 'year';
			case 251:
			case 250:
			case 252:
			case 249:
				return 'blob';
			default:
				return 'string';
		}
	}

	/**
	* Decodes the header for the resultset and returns the values found.
	* @param string The result to decode, by reference
	* @return array The errorNumber, the affectedRows, the insertId, the number of fields and the number of rows
	*/
	private function getResultSetHeader(&$result)
	{
		$arr = unpack('NerrNo/NaffectRows/NinsertId/NnumFields/NnumRows/c12dummy', $result);
		$result = substr($result, 32);

		return array($arr['errNo'], $arr['affectRows'], $arr['insertId'], $arr['numFields'], $arr['numRows']);
	}

	/**
	* Get the resultset field headers meta information and store this info
	* @param string The result to decode, by reference
	* @param int The number of fields in the result
	*/
	private function getResultSetFieldHeader(&$result, $numFields)
	{
		for ($x = 0; $x < $numFields; $x++)
		{
			$fieldName = $this->getString($result);
			$tableName = $this->getString($result);

			$arr = unpack('Ntype', $result);
			$result = substr($result, 4);

			$obj = new \stdClass();
			$obj->name = $fieldName;
			$obj->orgname = $fieldName;
			$obj->table = $tableName;
			$obj->orgtable = $tableName;

			//$type = $this->getFieldType();
			$obj->type = $arr['type'];

			$arr = unpack('Nflags/NfieldLength', $result);
			$result = substr($result, 8);

			//HTTPSQLFlag::getFlagsFromVal($arr['flags']));
			$obj->flags = $arr['flags'];
			$obj->max_length = $arr['fieldLength'];
			$obj->length = $arr['fieldLength'];

			$this->fields[] = $obj;
		}
	}

	/**
	* Gets the actual results and store these. Null values will be empty strings
	* @param string The result to decode, by reference
	* @param int The number of fields
	* @param int The number of rows
	*/
	private function getResultSets(&$result, $numFields, $numRows)
	{
		$this->results = new \System\Collection\Vector();
		for ($x = 0; $x < $numRows; $x++)
		{
			$obj = new \stdClass();
			for ($y = 0; $y < $numFields; $y++)
			{
				$name = $this->fields->get($y)->name;
				if (substr($result, 0, 1) == "\xFF")
				{
					$result = substr($result, 1);
					$obj->$name = "";
				}
				else
				{
					$obj->$name = $this->getString($result);
				}
			}

			$this->results[] = $obj;
		}
	}

	/**
	* Creates a new instance of this database
	* @param string The http tunnel to use
	* @param string The post string
	* @param \System\Db\Query The query
    * @param \System\Db\Database The database issueing the request
	*/
	public function __construct($httpTunnel, $postString, \System\Db\Query $query, \System\Db\HTTP\Database $database)
	{
		$this->requestIssuer = $database;
		$this->query = $query;
		$this->results = new \System\Collection\Vector();
		$this->fields = new \System\Collection\Vector();

		//increase its own querycounter
        self::$queryCount++;

        $timer = new \System\Calendar\Timer();
        $timer->start();

		//append the query
        $postString .= '&q[]=' . base64_encode($query->getQuery());
        $result = \System\HTTP\Request\Call::httpPageRequest($httpTunnel, $postString);
        $errorNumber = $this->decodeHeader($result);
        if ($errorNumber > 0)
        {
        	throw new \System\Error\Exception\DatabaseQueryException('Query: ' . $query->getQuery() . ' - ' . $this->getString($result));
		}
		else
		{
			while (strlen($result) > 1)
			{
				list($errorNo, $affectedRows, $insertId, $numFields, $numRows) = $this->getResultSetHeader($result);
				if ($errorNo == 0)
				{
					if ($numFields > 0)
					{
						$this->getResultSetFieldHeader($result, $numFields);
						$this->getResultSets($result, $numFields, $numRows);
					}
					else
					{
						$this->getString($result);
						//dont do anything with this info
					}
				}
				else
				{
					throw new \System\Error\Exception\DatabaseQueryException('Query: ' . $query->getQuery() . ' - ' . $this->getString($result));
				}
			}
		}

		$timer->stop();
        $this->duration = $timer->getDuration();
        self::$totalQueryTime += $this->duration;

        if (round($timer->getDuration()) >= self::SLOW_QUERY_TIME)
        {
            $event = new \System\Event\Event\OnSlowMySQLQueryEvent();
            $event->setQuery($query);
            $event->setDuration($this->duration);
            $event->raise($this);
        }

        $this->rewind();
	}

	/**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public function count()
    {
        return $this->results->count();
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public function current()
    {
        return $this->results->current();
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public function next()
    {
    	return $this->results->next();
	}

	/**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public function key()
    {
    	return $this->results->key();
	}

	/**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public function valid()
    {
    	return $this->results->valid();
	}

	/**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public function rewind()
    {
    	return $this->results->rewind();
	}

	/**
    * Sets the index of the resultset to a given index.
    * Note: this invalidates iterators and advanced the key to the next item
    * @param integer The index to point at.
    */
    public function pointTo($point)
    {
    	throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
	}

	/**
	* Gets the describtor fields from the resultset.
	* This will be returned as an array containing db field information
	* @rturn array Describtor information about the retrieved resultset
	*/
    public function getFields()
    {
    	return $this->fields->getArrayCopy();
	}
}