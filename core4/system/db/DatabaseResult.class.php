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


namespace System\Db;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Represents a collection of database result objects. The objects will be of the given resulttype, set in the \System\Db\Query object
* @package \System\Db
*/
class DatabaseResult extends \System\Collection\SecureVector
{
    /**
    * The amount of time in seconds before the query is considered slow.
    */
    const SLOW_QUERY_TIME = 3;

    /**
    * @var integer The amount of queries executed
    */
    protected static $queryCount = 0;

    /**
    * @var string The total amount of query execution time.
    */
    protected static $totalQueryTime = 0;

    /**
    * @var \MySQLi The databaselink
    */
    protected $databaseLink = null;
    /**
    * @var \System\Db\Query The query object to execute
    */
    protected $query = null;
    /**
    * @var Database The database that issues the request
    */
    protected $requestIssuer = null;

    /**
    * @var \MySQLi_Result The result of the query
    */
    protected $results;
    /**
    * @var integer The current key
    */
    protected $key = 0;
    /**
    * @var mixed The current selected item in the resultset
    */
    protected $current = null;
    /**
    * @var boolean indicates if the current selected item is valid for the iterator
    */
    protected $valid = false;

    /**
    * @var string The duration of the query
    */
    protected $duration = 0;

    /**
    * @var int The total amount of items in this set
    */
    protected $totalAmount = 0;

    /**
    * Creates a new database resultset. This function is automatically called by the database and should not
    * be called directly.
    * @param \MySQLi The link to the database system
    * @param \System\Db\Query The query
    * @param \System\Db\Database The database issueing the request
    */
    public function __construct(\MySQLi $databaseLink, \System\Db\Query $query, \System\Db\Database $database)
    {
        $this->databaseLink = $databaseLink;
        $this->requestIssuer = $database;
        $this->query = $query;

        //increase its own querycounter
        self::$queryCount++;

        $timer = new \System\Calendar\Timer();
        $timer->start();

        $actualQuery = $query->getQuery();

        if (!$this->results = $databaseLink->query($actualQuery))
        {
            throw new \System\Error\Exception\DatabaseQueryException('Query: ' . $actualQuery . ' - ' . $databaseLink->error);
        }

        /**
        * If there is a query that wants to execute the amount of found rows, we store this amount in the own vector.
        * Do note that this query gets logged before the actual query, because of the stackframe buildup. The actual execution order is correct
        */
        if (strpos($actualQuery, 'SQL_CALC_FOUND_ROWS') !== false)
        {
            $query = new \System\Db\Query($database, 'SELECT FOUND_ROWS() AS amount');
            $this->totalAmount = $database->queryScalar($query)->first();
        }
        else
        {
            $this->totalAmount = $this->count();
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
    * Reverses the current collection. We dont support sorting DatabaseResults
    * @return null Nothing
    */
    public function reverse()
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Merges another collection into this collection.
    * Not supported on DatabaseResults
    */
    public function combine(\System\Collection\iCollection $collection)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Returns the duration of the query
    * @return string The duration of the query in seconds
    */
    public final function getQueryTime()
    {
        return $this->duration;
    }

    /**
    * Returns the global total amount of DB query execution time. This number is the total time over all queries executed on all
    * connected database objects.
    * @return string The total amount of execution time for all DB queries.
    */
    public static final function getTotalQueryTime()
    {
        return self::$totalQueryTime;
    }

    /**
    * Returns the total amount of queries executed on the system during a single run.
    * @return int The total amount of queries.
    */
    public static final function getTotalQueryCount()
    {
        return self::$queryCount;
    }

    /**
    * Returns the amount of items in the current collection
    * @return int The amount of items in the current collection
    */
    public function count()
    {
        if (!is_bool($this->results))
        {
            return $this->results->num_rows;
        }
        else
        {
            return 0;
        }
    }

	/**
	* Returns the total amount of entries in the set, including those that are potentially available.
	* That means this number is the total amount of results, regardless of the limit parameter in your query.
	* For this to fully work, a SQL_CALC_FOUND_ROWS attribute needs to be set in the query, otherwise the fetched amount
	* is returned
	* @return int The amount of items in the query
	*/
    public function getTotalAmount()
    {
    	if (!is_bool($this->results))
        {
            return $this->totalAmount;
        }
        else
        {
            return 0;
        }
    }

    /**
    * Creates an array with the contents of the collection.
    * Resets the internal pointer
    * @return array A new array with the contents of the collection
    */
    public function getArrayCopy()
    {
        $array = array();

        foreach ($this as $value)
        {
            $array[] = $value;
        }

        $this->rewind();

        return $array;
    }

    /**
    * Searches for the given value in the collections and returns if it is present or not.
    * @param mixed The value to search for. Note this is a full result object.
    * @param bool If the second parameter strict is set to TRUE, then the function will also check the types of the needle.
    * @return bool Boolean indicating if the object is present in the collection.
    */
    public function contains($needle, $strict = false)
    {
        $found = false;

        foreach ($this as $value)
        {
            if ($strict)
            {
                $found = ($value === $needle);
            }
            else
            {
                $found = ($value == $needle);
            }

            if ($found)
            {
                break;
            }
        }

        $this->rewind();

        return $found;
    }

    /**
    * Replaces the entire contents of this collection by the given collection
    * @param \System\Collection\iCollection The collection to replace the current one with
    */
    public final function exchangeCollection(\System\Collection\iCollection $input)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Replaces the entire contents of this collection by the given array
    * @param array An array to replace the contents of this collection
    * @return array An array with the previous collection
    */
    public final function exchangeArray(array $input)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Serializes the object
    * @return string The serialized object
    */
    public final function serialize()
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Unserialize the given parameter and store it in the collection
    * @param string The string to deserialize
    */
    public final function unserialize($serialized)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Gets the current selected value from the collection.
    * @return mixed The current selected value from the collection
    * @see The build-in \current() function
    */
    public function current()
    {
        return $this->current;
    }

    /**
    * Increments the current collection pointer
    * @see The build-in \next() function
    */
    public function next()
    {
        $object = $this->query->getResultType();
        if ($object == null)
        {
            $this->current = $this->results->fetch_object();
        }
        else
        {
            //we apply the database to all dynamicbase objects
            if (is_subclass_of($this->query->getResultType(), '\System\Base\DynamicBaseObj'))
            {
            	$this->current = $this->results->fetch_object($this->query->getResultType(), array($this->requestIssuer));
            }
            else
            {
                $this->current = $this->results->fetch_object($this->query->getResultType());
            }
        }

        $this->valid = !is_null($this->current);
        $this->key++;
    }

    /**
    * Gets the current key from the collection.
    * @return mixed The current key
    * @see The build-in \key() function
    */
    public function key()
    {
        return $this->key;
    }

    /**
    * Validates the current entry in the collection
    * @return boolean Whether or not the current key is valid
    * @see The build-in \valid() function
    */
    public function valid()
    {
        return $this->valid;
    }

    /**
    * Sets the index of the resultset to a given index.
    * Note: this invalidates iterators and advanced the key to the next item
    * @param integer The index to point at.
    */
    public function pointTo($point)
    {
        $val = new \System\Security\Validate();
        if (($this->count() > 0) &&
            ($val->isInt($point, 'point', 0, null, true) == \System\Security\ValidateResult::VALIDATE_OK) &&
            ($this->count() > $point))
        {
            $this->key = $point;
            $this->results->data_seek($point);
            $this->next();
        }
    }

    /**
    * Rewinds the internal collection
    * @see The build-in \rewind() function
    */
    public function rewind()
    {
        if ($this->count() > 0)
        {
            if (!is_null($this->key))
            {
                $this->results->data_seek(0);
            }

            $this->key = -1;
            $this->next();
        }
    }

    /**
    * Checks if the given offset exists
    * @param mixed The index to check
    * @return boolean Returns whether or not he index exists
    */
    public function offsetExists($offset)
    {
        return $this->count() > $offset;
    }

    /**
    * Retrieves the value from the given index.
    * @param mixed The index to return
    * @return mixed The value on that specific index
    */
    public function offsetGet($offset)
    {
        $value = null;
        if ($this->offsetExists($offset))
        {
            $this->pointTo($offset);
            $value = $this->current();
            $this->rewind();
        }

        return $value;
    }

    /**
    * Sets the value at the given offset
    * @param mixed The index to be used
    * @param mixed The value to place at the index
    */
    public final function offsetSet($offset, $value)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * Removes the given value and index
    * @param mixed The index te remove
    */
    public final function offsetUnset($offset)
    {
        throw new \System\Error\Exception\DatabaseQueryException('This operation cannot be executed on a database result');
    }

    /**
    * This function returns the current executed query object
    * @return \System\Db\Query Returns the query object
    */
    public final function getQuery()
    {
        return $this->query;
    }

	/**
	* Gets the describtor fields from the resultset.
	* This will be returned as an array containing db field information
	* @rturn array Describtor information about the retrieved resultset
	*/
    public function getFields()
    {
    	if ($this->results instanceof \MySQLi_Result)
    	{
            return $this->results->fetch_fields();
        }

        return array();
    }
}