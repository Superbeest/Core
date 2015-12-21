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


namespace System\System\Introspection;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the functionality to fingerprint a database
* @package \System\System\Introspection
*/
class Database extends \System\Base\StaticBase
{
	/**
	* Returns the hashes for the given database.
	* This function gives the hash per table in the database connection.
	* The current autoincrement is discarded in the matching.
	* @param \System\Db\Database The databaseconnection to use
	* @return \System\Collection\Map A map with hashes per table
	*/
	public static final function getTableHashes(\System\Db\Database $db)
	{
		$map = new \System\Collection\Map();

		$tableNames = \System\Db\Meta\Meta::c_getTableNames($db);
		foreach ($tableNames as $tableName)
		{
			$createQuery = \System\Db\Meta\Meta::getCreateQuery($db, $tableName);
			//remove the autoincrement component
			$createQuery = preg_replace('/(\ AUTO_INCREMENT=\d+)/im', '', $createQuery);

			$hash = new \System\Security\Hash(\System\Security\Hash::HASH_MD5);
			$hash->addString($createQuery);

			$map->set($tableName, $hash->getHash());
		}

		return $map;
	}
}