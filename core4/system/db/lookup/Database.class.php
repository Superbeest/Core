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


namespace System\Db\Lookup;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the DBLookup class for database credentials
* @package \System\Db\Lookup
*/
final class Database extends \System\Base\DynamicBaseObj
{
	/**
	* The default port for database connections
	*/
	const DB_PORT_DEFAULT = 3306;
	/**
	* The default db persistancy setting
	*/
	const DB_PERSISTANT_DEFAULT = false;

	/**
	* @publicget
	* @publicset
	* @validatehandle
	* This function is not for direct usage and should not be called. Refer to getDbLookup()
	* @see getDBLookup()
	* @var \System\Collection\Map The cache to store all the lookup entries in.
	*/
	protected static $lookupCache = null;

	/**
    * Returns the full path to the XML file for the object
    * @return string The full path to the XML tree file
    */
    public static function getXMLSourceFile()
    {
        return PATH_SYSTEM . 'db/lookup/database.xml';
    }

	/**
	* Returns the corresponding Lookup database instance
	* @return \System\Db\Database The database instance
	*/
	public function getLookupDatabase()
    {
    	return \System\Db\Database::getConnection($this->getDbServer(), $this->getDbUser(), $this->getDbPassword(), $this->getDbName(), $this->getDbPort(), $this->getDbPersistent());
	}

	/**
	* Creates a new DbLookup object.
	* @param Database The database to create the connection in. This should be the DbLookup pool
	* @param string The unique name of the DbLookup entry
	* @param string The server or db host
	* @param string The connection user
	* @param string The connection password
	* @param string The name of the database used
	* @param int The port for the database connection
	* @param bool True to use persistant connections
	* @return DbLookup The newly created DbLookup object
	*/
    public static final function create(\System\Db\Database $db, $name, $dbServer, $dbUser, $dbPassword, $dbName, $dbPort = self::DB_PORT_DEFAULT, $persistent = self::DB_PERSISTANT_DEFAULT)
	{
		$previousAutocommit = $db->getAutocommit();
		$db->setAutocommit(false);

		$query = new \System\Db\Query($db, SQL_DBLOOKUP_CREATE);
		$query->bind($name, \System\Db\QueryType::TYPE_STRING);

		$db->query($query);
		$insertId = $db->getInsertId();

		$dbLookup = self::loadPrimary($db, $insertId);
		assert($dbLookup);

		$dbLookup->setDbName($dbName)
			->setDbServer($dbServer)
			->setDbUser($dbUser)
			->setDbPassword($dbPassword)
			->setDbPort($dbPort)
			->setDbPersistent($persistent);
		$dbLookup->storePrimary();

		$db->setAutocommit($previousAutocommit);

		return $dbLookup;
	}

	/**
	* Returns an instance of the Databse from the given DbLookup entry with the given name.
	* This name is an uniquely identifyable name and represent a database connection.
	* This function requires the DBPOOL_DB_HOST, DBPOOL_DB_USER, DBPOOL_DB_PASS, DBPOOL_DB_NAME to be set.
	* This function uses caching and preloads any
	* @param string The name of the DbLookup entry
	* @return Database The requested instance of the database
	*/
	public static final function getDbLookup($name)
	{
		if ((defined('DBPOOL_DB_HOST')) &&
			(defined('DBPOOL_DB_USER')) &&
			(defined('DBPOOL_DB_PASS')) &&
			(defined('DBPOOL_DB_NAME')))
		{
			$cache = self::getLookupCache();
			if (!$cache->hasItems())
			{
				$db = \System\Db\Database::getConnection(DBPOOL_DB_HOST, DBPOOL_DB_USER, DBPOOL_DB_PASS, DBPOOL_DB_NAME);
				$lookups = self::load($db, 'all', null, true);
				foreach ($lookups as $lookup)
				{
					$cache->set($lookup->getName(), $lookup);
				}
			}

			if ($cache->keyExists($name))
			{
				return $cache->$name->getLookupDatabase();
			}

			throw new \System\Error\Exception\SystemException('The given DB Lookup does not exists, or is not properly defined in the lookup pool: ' . $name);
		}
		else
		{
			throw new \System\Error\Exception\SystemException('The DBPOOL_* connection parameters are not all set. Please verify the settings');
		}
	}
}