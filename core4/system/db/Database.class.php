<?php
/**
* Database.class.php
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
* Class that provides access to the database system.
* @package \System\Db
*/
class Database extends \System\Base\BaseObj
{
    /**
    * The default charset to use for the database connection
    */
    const DEFAULT_CHARSET_ENCODING = 'utf8';

    /**
    * @var \System\Collection\Map The handle to the server collection
    */
    private static $connectionMap = null;

    /**
    * @var integer The amount of queries over the active db connection
    */
    protected $dbQueryCount = 0;
    /**
    * @var \MySQLi The connection to the database
    */
    private $databaseLink = null;
    /**
    * @publicget
    * @var string The unique identifier for this databaseconnection
    */
    protected $identifierString = '';

    /**
    * @publicset
    * @publicget
    * @var \System\Db\Database Sets a secondary \System\Db\Database connection pipe to use.
    */
    protected $secondaryPipe = null;

    /**
    * Validates the handle to the map object and creates one if needed.
    * @return \System\Collection\Map The handle to the datastorage
    */
    protected final static function validateHandle()
    {
        if (self::$connectionMap == null)
        {
            self::$connectionMap = new \System\Collection\Map();
        }

        return self::$connectionMap;
    }

	/**
	* This function handles the shutdown of all the databases, with respect to
	* the autocommit. If the autocommit is disabled, then all changes will be
	* reverted.
	* This function should not be called manually.
	*/
    public static final function handleShutdown()
    {
    	$connections = self::validateHandle();
    	foreach ($connections as $connection)
    	{
			/** @var \System\Db\Database */
    		if (!$connection->getAutocommit())
    		{
    			$connection->rollback();
			}

			$connection->close();
		}
	}

    /**
    * This function outputs a database connection with the given paramters.
    * If the connection to a database with the given parameters has been made before, then the original connection will be used. No duplicate connections
    * to the same database with the same parameters will be made, thus enforcing a persistend connection.
    * @param string The URL or IP to the database host
    * @param string The username used to log in to the database
    * @param string The password used to log in to the database
    * @param string The name of the database to connect to
    * @param integer The port to use for the connection
    * @param bool True to use persistant connections, false otherwise
    * @return \System\Db\Database The database object
    */
    public static final function getConnection($databaseHost = DATABASE_HOST, $databaseUser = DATABASE_USER, $databasePassword = DATABASE_PASS, $databaseName = DATABASE_NAME, $databasePort = DATABASE_PORT, $persistant = DATABASE_PERSISTANT)
    {
		//is_bool() expects a variable, not a define
        if (!is_bool($persistant))
        {
            throw new \System\Error\Exception\DatabaseConnectionException('Invalid database parameters; could not connect. $persistant parameter is not of boolean type.');
        }

        $databaseHost = $persistant ? 'p:' . $databaseHost : $databaseHost;

        $map = self::validateHandle();

        //we create connection object to store some information and make it easy to generate a new key
        $connectionObject = new \stdClass();
        $connectionObject->databaseHost = $databaseHost;
        $connectionObject->databaseUser = $databaseUser;
        $connectionObject->databasePassword = $databasePassword;
        $connectionObject->databaseName = $databaseName;
        $connectionObject->databasePort = $databasePort;

        $connectionString = serialize($connectionObject);
        if (!isset($map[$connectionString]))
        {
             $map[$connectionString] = new Database($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort, md5($connectionString));
        }

        return $map[$connectionString];
    }

    /**
    * Tries to ping the server and reconnects if needed.
    * @return bool Whether or not the ping and optional reconnect attempt was successfull
    */
    public function ping()
    {
        return $this->databaseLink->ping();
    }

    /**
    * Sanitizes the given value for database insertion using the database character encoding set.
    * Note: This function should not be used to sanitize user input, nor regular variables.
    * @param string The string to sanitize
    * @return string The sanitized string
    */
    public function sanitize($value)
    {
        return $this->databaseLink->real_escape_string($value);
    }

    /**
    * String representation of the current object
    * @return string A string representation of the current object
    */
    public function __toString()
    {
        return get_class($this) . '(' . $this->identifierString . ')';
    }

    /**
    * The constructor of the database object. This creates the actual connection and makes the database system go in a ready state.
    * @param string The URL or IP to the database host, when prefixed with 'p:' it will be a persistant connection
    * @param string The username used to log in to the database
    * @param string The password used to log in to the database
    * @param string The name of the database to connect to
    * @param integer The port to use for the connection
    * @param string A unique string to identify the databaseconnection
    */
    protected function __construct($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort, $identifierString)
    {
        if (!class_exists('MySQLi'))
        {
            throw new \System\Error\Exception\SystemException('The required PHP module MySQLi has not been loaded.');
        }

        $this->databaseLink = new \MySQLi($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort);
        $this->databaseLink->set_charset(self::DEFAULT_CHARSET_ENCODING);

        $this->identifierString = $identifierString;

        if ($this->databaseLink->connect_error)
        {
            throw new \System\Error\Exception\DatabaseConnectionException('Invalid database parameters; could not connect');
        }
    }

	/**
	* Closes the database connection. This invalidates the current Database instance.
	* The destructor does not explicitly close the connection, thus this function needs to be called in order to close the connection.
	* When this function is not used, the database connection stays open until the end of the script.
	*/
    public function close()
    {
    	$this->databaseLink->close();
	}

    /**
    * Executes a query on the database system.
    * @param \System\Db\Query The query to be executed
    * @return \System\Db\DatabaseResult A collection of result from the database.
    */
    public function query(\System\Db\Query $query)
    {
        if (($query->getUseSecondaryPipe()) &&
            ($this->secondaryPipe instanceof \System\Db\Database))
        {
            $query->setUseSecondaryPipe(false);
            return $this->secondaryPipe->query($query);
        }
        else
        {
            $result = null;

            //we increase the amount of db queries
            $this->dbQueryCount++;
            $result = new \System\Db\DatabaseResult($this->databaseLink, $query, $this);

            $event = new \System\Event\Event\OnMySQLQueryEvent();
            $event->setQuery($query);
            $event->setResult($result);
            $event->raise($this);

            return $result;
        }
    }

    /**
    * Executes a query on the database system and tries to return it as a map
    * The result will be converted to a \System\Collection\Map
    * The first field will be used as the key while the second field will be the value
    * If the number of fields retrieved from the query does not equal 2 an exception will be thrown
    * @param Query The query to be executed
    * @return \System\Collection\Map A map
    */
    public final function queryMap(\System\Db\Query $query)
    {
        $results = $this->query($query);

        $map = new \System\Collection\Map();

        if (count($results->getFields()) == 2)
        {
            $fields = $results->getFields();

            foreach ($results as $result)
            {
                $key = $fields[0]->name;
                $value = $fields[1]->name;

                $map->set($result->$key, $result->$value);
            }
        }
        else
        {
            throw new \System\Error\Exception\DatabaseQueryException('Given query should have 2 fields but has: ' . $results->getFields());
        }

        return $map;
    }

    /**
    * Executes a query on the database system and tries to return it as a vector
    * The result will be converted to a \System\Collection\Vector.
    * The first field will be added as the value.
    * If the number of fields retrieved from the query does not equal 1 an exception will be thrown.
    * @param Query The query to be executed
    * @return \System\Collection\Vector A Vector
    */
    public final function queryScalar(\System\Db\Query $query)
    {
        $results = $this->query($query);

        $vec = new \System\Collection\Vector();

        if (count($results->getFields()) == 1)
        {
            $fields = $results->getFields();

            foreach ($results as $result)
            {
                $value = $fields[0]->name;
                $vec->add($result->$value);
            }
        }
        else
        {
            throw new \System\Error\Exception\DatabaseQueryException('Given query should have 1 field but has: ' . count($results->getFields()));
        }

        return $vec;
    }

    /**
    * Executes a query on the database system. If there is only one result it returns this result.
    * If there are no results, a null is returned. On multiple results, an exception is thrown; use query() instead.
    * Except from this behaviour, this function is identical to the query() function.
    * @param \System\Db\Query The query to be executed.
    * @return mixed A single result object
    */
    public final function querySingle(\System\Db\Query $query)
    {
        $result = $this->query($query);

        switch ($result->count())
        {
            case 1:
                return $result->current();
            case 0:
                return null;
        }

        throw new \System\Error\Exception\DatabaseQueryException('Given query produced more than 1 result: ' . $result->count() . ' results given.');
    }

    /**
    * Retrieves the current database characterset.
    * @return string The current characterset
    */
    public function getCharacterSet()
    {
        return $this->databaseLink->character_set_name();
    }

    /**
    * Sets the current database characterset.
    * @param string The new characterset
    */
    public function setCharacterSet($characterSet = \System\Db\Database::DEFAULT_CHARSET_ENCODING)
    {
        $this->databaseLink->set_charset($characterSet);
    }

	/**
	* Disable foreign key constraints.
	*/
    public final function disableForeignKeyChecks()
    {
    	$query = new \System\Db\Query($this, 'SET foreign_key_checks = 0');
        $this->query($query);
	}

	/**
	* Enable foreign key constraints (default)
	*/
	public final function enableForeignKeyChecks()
	{
		$query = new \System\Db\Query($this, 'SET foreign_key_checks = 1');
        $this->query($query);
	}

    /**
    * Checks whether or not the autocommit is enabled. By default this is true.
    * Note that executing this function requires a transactional database engine.
    * Also note that executing this function exectues a query on the database. Therefor, use this function sparingly.
    * @return boolean True if the autocommit is enabled, false otherwise.
    */
    public function getAutocommit()
    {
        $query = new \System\Db\Query($this, 'SELECT @@autocommit AS auto');
        $results = $this->query($query);
        if ($results->count() == 1)
        {
            return $results->current()->auto == 1;
        }

        throw new \System\Error\Exception\DatabaseQueryException('Only supported on transactional systems. MyISAM or ISAM are not supported.');
    }

    /**
    * Enables or disables the autocommit functionality for the current database.
    * This only works on database engines with support for transactions. MyISAM or ISAM are thus not supported.
    * Note that when enabling the autocommit functionality, all open queries are committed to the database!
    * @param boolean Enable or disable the autocommit functionality
    */
    public function setAutocommit($enable = true)
    {
        if (!$this->databaseLink->autocommit($enable))
        {
            throw new \System\Error\Exception\DatabaseQueryException('Only supported on transactional systems. MyISAM or ISAM are not supported.');
        }
    }

    /**
    * Commits the current open queries. After commit, the changes are permanent.
    * @return boolean Returns true on success, false on failure.
    */
    public function commit()
    {
        return $this->databaseLink->commit();
    }

    /**
    * Does a rollback on the current open queries.
    * @return boolean Returns true on success, false on failure.
    */
    public function rollback()
    {
        return $this->databaseLink->rollback();
    }

    /**
    * Returns the global total amount of DB queries executed. This number is the total amount of queries over all the existing
    * database objects.
    * @return integer The total amount of DB queries executed over all databases in a single run.
    */
    public static final function getTotalQueryCount()
    {
        return \System\Db\DatabaseResult::getTotalQueryCount();
    }

    /**
    * Returns the global total amount of DB query execution time. This number is the total time over all queries executed on all
    * connected database objects.
    * @return string The total amount of execution time for all DB queries.
    */
    public static final function getTotalQueryTime()
    {
        return \System\Db\DatabaseResult::getTotalQueryTime();
    }

    /**
    * Returns the amount of queries executed over this current database.
    * @return integer The amount of queries executed in the current database.
    */
    public final function getQueryCount()
    {
        return $this->dbQueryCount;
    }

    /**
    * Gets the number of affected rows in a previous MySQL operation
    * @return integer Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query
    */
    public final function getAffectedRows()
    {
        return $this->databaseLink->affected_rows;
    }

    /**
    * Returns the auto generated id used in the last query.
    * @return integer Returns the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute. If the last query wasn't an INSERT or UPDATE statement or if the modified table does not have a column with the AUTO_INCREMENT attribute, this function will return zero.
    */
    public final function getInsertId()
    {
        return $this->databaseLink->insert_id;
    }
}
