<?php
/**
* Meta.class.php
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


namespace System\Db\Meta;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the queue for a database table
* @package \System\Db\Meta
*/
final class Meta extends \System\Base\BaseObj
{
	/**
	* @var string The name of the table.
	*/
	private $Name;
	/**
	* @var string The storage engine for the table.
	*/
	private $Engine;
	/**
	* @var string The version number of the table's .frm file.
	*/
	private $Version;
	/**
	* @var string The row-storage format (Fixed, Dynamic, Compressed, Redundant, Compact). For MyISAM tables, (Dynamic corresponds to what myisamchk -dvv reports as Packed. Starting with MySQL/InnoDB 5.0.3, the format of InnoDB tables is reported as Redundant or Compact. Prior to 5.0.3, InnoDB tables are always in the Redundant format.
	*/
	private $Row_format;
	/**
	* @var int The number of rows. Some storage engines, such as MyISAM, store the exact count. For other storage engines, such as InnoDB, this value is an approximation, and may vary from the actual value by as much as 40 to 50%. In such cases, use SELECT COUNT(*) to obtain an accurate count.
	*/
	private $Rows;
	/**
	* @var int The average row length.
	*/
	private $Avg_row_length;
	/**
	* @var int The length of the data file.
	*/
	private $Data_length;
	/**
	* @var int The maximum length of the data file. This is the total number of bytes of data that can be stored in the table, given the data pointer size used.
	*/
	private $Max_data_length;
	/**
	* @var int The length of the index file.
	*/
	private $Index_length;
	/**
	* @var int The number of allocated but unused bytes.
	*/
	private $Data_free;
	/**
	* @var int The next AUTO_INCREMENT value.
	*/
	private $Auto_increment;
	/**
	* @var string When the table was created.
	*/
	private $Create_time;
	/**
	* @var string When the data file was last updated. For some storage engines, this value is NULL. For example, InnoDB stores multiple tables in its tablespace and the data file timestamp does not apply. For MyISAM, the data file timestamp is used; however, on Windows the timestamp is not updated by updates so the value is inaccurate.
	*/
	private $Update_time;
	/**
	* @var string When the table was last checked. Not all storage engines update this time, in which case the value is always NULL.
	*/
	private $Check_time;
	/**
	* @var string The table's character set and collation.
	*/
	private $Collation;
	/**
	* @var string The live checksum value (if any).
	*/
	private $Checksum;
	/**
	* @var string Extra options used with CREATE TABLE. The original options supplied when CREATE TABLE is called are retained and the options reported here may differ from the active table settings and options.
	*/
	private $Create_options;
	/**
	* @var string The comment used when creating the table (or information as to why MySQL could not access the table information).
	*/
	private $Comment;

	/**
	* Load a \System\Db\Meta\Meta object with the given tablename
	* @param \System\Db\Database The database to query
	* @param string the table name to search for
	* @return \System\Db\Meta\Meta the found object
	*/
	public static function loadPrimary(\System\Db\Database $db, $tableName)
	{
		$query = new \System\Db\Query($db, 'SHOW TABLE STATUS LIKE %?%');
		$query->bind($tableName, \System\Db\QueryType::TYPE_STRING);
		$query->setResultType('\System\Db\Meta\Meta');

		$meta = $db->querySingle($query);

		return $meta;
	}

	/**
	* Returns the create SQL query to reproduce the given table.
	* @param \System\Db\Database The database to query
	* @param string The name of the table to work with
	* @return string The create SQL query
	*/
	public static function getCreateQuery(\System\Db\Database $db, $tableName)
	{
		$query = new \System\Db\Query($db, 'SHOW CREATE TABLE ' . $tableName);
		$result = $db->querySingle($query);

		//get the specific key in the result
		$key = 'Create Table';
		return $result->$key;
	}

	/**
	* Load all meta objects from the given database
	* @param \System\Db\Database The database to query
	* @param string A prefix for the tables. No prefix returns all
	* @return \System\Collection\Vector the meta objects
	*/
	public static function load(\System\Db\Database $db, $prefix = '')
	{
		if (empty($prefix))
		{
			$query = new \System\Db\Query($db, 'SHOW TABLE STATUS');
		}
		else
		{
			$query = new \System\Db\Query($db, 'SHOW TABLE STATUS LIKE %?%');
			$query->bind($prefix . '%', \System\Db\QueryType::TYPE_STRING);
		}

		$query->setResultType('\System\Db\Meta\Meta');

		$results = $db->query($query);

		return $results;
	}

	/**
	* Get the table name
	* @return string the table name
	*/
	public function getName()
	{
		return $this->Name;
	}

	/**
	* Get the collation of the current table
	* @return string the collation
	*/
	public function getCollation()
	{
		return $this->Collation;
	}

	/**
	* Returns the DB engine
	* @return string The engine
	*/
	public function getEngine()
	{
		return $this->Engine;
	}

	/**
	* The version number of the table's .frm file.
	* @return string The version
	*/
	public function getVersion()
	{
		return $this->Version;
	}

	/**
	* The row-storage format (Fixed, Dynamic, Compressed, Redundant, Compact). For MyISAM tables, (Dynamic corresponds to what myisamchk -dvv reports as Packed. Starting with MySQL/InnoDB 5.0.3, the format of InnoDB tables is reported as Redundant or Compact. Prior to 5.0.3, InnoDB tables are always in the Redundant format.
	* @return string The row format
	*/
	public function getRowFormat()
	{
		return $this->Row_format;
	}

	/**
	* The number of rows. Some storage engines, such as MyISAM, store the exact count. For other storage engines, such as InnoDB, this value is an approximation, and may vary from the actual value by as much as 40 to 50%. In such cases, use SELECT COUNT(*) to obtain an accurate count.
	* @return int The rows
	*/
	public function getRows()
	{
		return $this->Rows;
	}

	/**
	* When the table was created.
	* @return string The time of creation
	*/
	public function getCreateTime()
	{
		return $this->Create_time;
	}

	/**
	* The live checksum value (if any).
	* @return string The checksum
	*/
	public function getChecksum()
	{
		return $this->Checksum;
	}

	/**
	* When the data file was last updated. For some storage engines, this value is NULL. For example, InnoDB stores multiple tables in its tablespace and the data file timestamp does not apply. For MyISAM, the data file timestamp is used; however, on Windows the timestamp is not updated by updates so the value is inaccurate.
	* @return string The time of last update
	*/
	public function getUpdateTime()
	{
		return $this->Update_time;
	}

	/**
	* When the table was last checked. Not all storage engines update this time, in which case the value is always NULL.
	* @return string The last check time
	*/
	public function getCheckTime()
	{
		return $this->Check_time;
	}

	/**
	* The comment used when creating the table (or information as to why MySQL could not access the table information).
	* @return string The comment
	*/
	public function getComment()
	{
		return $this->Comment;
	}

	/**
	* The average row length.
	* return int The average row length.
	*/
	public function getAverageRowLength()
	{
		return $this->Avg_row_length;
	}

	/**
	* Returns The maximum length of the data file. This is the total number of bytes of data that can be stored in the table, given the data pointer size used.
	* @return int The maxiumum data length
	*/
	public function getMaxDataLength()
	{
		return $this->Max_data_length;
	}

	/**
	* The length of the index file.
	* @return int The index length
	*/
	public function getIndexLength()
	{
		return $this->Index_length;
	}

	/**
	* The length of the data file.
	* @return int The length of the data file.
	*/
	public function getDataLength()
	{
		return $this->Data_length;
	}

	/**
	* Returns the number of allocated but unused bytes.
	* @return int The length of the free but reserved space
	*/
	public function getDataFree()
	{
		return $this->Data_free;
	}

	/**
	* Get the next auto increment value
	* @return int The next AUTO_INCREMENT value
	*/
	public function getAutoIncrement()
	{
		return $this->Auto_increment;
	}

	/**
	* Set the collation of the current table
	* @param \System\Db\Database The database to query
	* @param string the new collation type
	*/
	public function setCollation(\System\Db\Database $db, $collation)
	{
		$query = new \System\Db\Query($db, 'ALTER TABLE %?% COLLATE %?%');
		$query->bind($this->getName(), \System\Db\QueryType::TYPE_QUERY);
		$query->bind($collation, \System\Db\QueryType::TYPE_STRING);

		$db->query($query);

		$this->Collation = $collation;
	}

      /**
    * Returns a Vector with all tablesnames from the requested database
    * @param \System\Db\Database the database to query
    * @return \System\Collection\Vector a vector containing all tablenames
    */
	public static final function getTableNamesFromDatabase(\System\Db\Database $db, $databaseName)
    {
        $query = new \System\Db\Query($db, 'SHOW TABLES IN %?%');
        $query->bind($databaseName, \System\Db\QueryType::TYPE_QUERY);
        $results = $db->queryScalar($query);

        return $results;
    }
    
    /**
	* Returns a Vector with all tablesnames from the current database
	* @param \System\Db\Database the database to query
	* @param string A prefix for the tables. No prefix returns all
	* @return \System\Collection\Vector a vector containing all tablenames
	*/
	public static final function getTableNames(\System\Db\Database $db, $prefix = '')
	{
		$query = new \System\Db\Query($db, 'SHOW TABLES');
		$results = $db->queryScalar($query);

		if (!empty($prefix))
		{
			$vec = new \System\Collection\Vector();
			foreach ($results as $result)
			{
				if (stripos($result, $prefix) === 0)
				{
					$vec[] = $result;
				}
			}
			return $vec;
		}

		return $results;
	}

	/**
	* Returns a Vector with all the database names from the current connection.
	* The database given will be used to retrieve all the databases the current connected user has access to.
	* @param \System\Db\Database The connection used to poll the user's accessable databases
	* @param string A prefix for the databases. No prefix returns all
	* @return \System\Collection\Vector A Vector containing all databases
	*/
	public static final function getDatabaseNames(\System\Db\Database $db, $prefix = '')
	{
		$query = new \System\Db\Query($db, 'SHOW DATABASES');
		$results = $db->queryScalar($query);

		if (!empty($prefix))
		{
			$vec = new \System\Collection\Vector();
			foreach ($results as $result)
			{
				if (stripos($result, $prefix) === 0)
				{
					$vec[] = $result;
				}
			}
			return $vec;
		}

		return $results;
	}

	/**
	* Returns a Vector with all table column names from the requested table
	* @param \System\Db\Database the database to query
	* @param string The tablename to retrieve the columns from
	* @return \System\Collection\Vector a Vector containing all the column names
	*/
	public static final function getTableColumnNames(\System\Db\Database $db, $tableName)
	{
		$query = new \System\Db\Query($db, 'SHOW COLUMNS FROM %?%');
		$query->bind($tableName, \System\Db\QueryType::TYPE_QUERY);

		$results = $db->query($query);

		$columnNames = new \System\Collection\Vector();

		foreach ($results as $result)
		{
			$columnNames[] = $result->Field;
		}

		return $columnNames;
	}
}