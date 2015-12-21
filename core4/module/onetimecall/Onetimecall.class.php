<?php
/**
* Onetimecall.class.php
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

namespace Module\Onetimecall;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* The implementation for the Onetimecall
* @package \Module\Onetimecall
*/
class Onetimecall extends \System\Base\DynamicBaseObj
{
	const MAX_AGE = 3;

	/**
	* @var \System\Collection\Map The registered calls
	*/
	private static $registeredCalls = null;

	/**
	* Gets the registered calls in a map.
	* The key represents the dbkey, the value is the callback
	* @return \System\Collection\Map The calls
	*/
	public static final function getRegisteredCalls()
	{
		if (!self::$registeredCalls)
		{
			self::$registeredCalls = new \System\Collection\Map();
		}

		return self::$registeredCalls;
	}

	/**
	* Registers a given callback to a key
	* @param string The key to attach the callback to
	* @param array The callback to call
	*/
	public static final function addRegisteredCall($key, $callback)
	{
		if (!self::$registeredCalls)
		{
			self::$registeredCalls = new \System\Collection\Map();
		}

		self::$registeredCalls[$key] = $callback;
	}

	/**
    * Returns the full path to the XML file for the object
    * @return string The full path to the XML tree file
    */
    public static final function getXMLSourceFile()
    {
        return PATH_MODULES . 'onetimecall\onetimecall.xml';
    }

    /**
    * Create a new onetimecall
    * @param \System\Db\Database The database to query
    * @param string The key which we should use to find the appropriate callback
    * @param string The value to reference the object with
    * @return \Module\Onetimecall\Onetimecall the newly created instance
    */
	public static function create(\System\Db\Database $db, $key, $value)
	{
		$hash = new \System\Security\Hash();
		$hash->addString(\System\Calendar\Time::now());

		$token = $hash->getHash();

		$query = new \System\Db\Query($db, SQL_ONETIMECALL_CREATE);
		$query->bind($token, \System\Db\QueryType::TYPE_STRING);
		$query->bind($key, \System\Db\QueryType::TYPE_STRING);
		$query->bind((string)$value, \System\Db\QueryType::TYPE_STRING);

		$db->query($query);

		$insertId = $db->getInsertId();

		return \Module\Onetimecall\Onetimecall::loadPrimary($db, $insertId);
	}

	/**
	* Get the timestamp of the onetimecall object
	* @return \System\Calendar\Time the converted timestamp object
	*/
	public final function getTimestamp()
	{
		return \System\Calendar\Time::fromMySQLTimestamp($this->internal('getTimestamp'));
	}

	/**
	* Checks if the current onetimecall is still valid and not overdue
	* @param mixed the maximum age of the onetimecall
	* @return boolean Wether the url is still callable or not
	*/
	public final function isCallable($maxAge = self::MAX_AGE)
	{
		$mintime = new \System\Calendar\Time();
		$mintime->substractDay($maxAge);

		return ($mintime->compare($this->getTimestamp()) == \System\Math\Math::COMPARE_GREATERTHAN);
	}

	/**
	* Delete an onetimecall. This invalidates the object in the db.
	* @param \System\Db\Database The database to query
	* @return null so we stop the chaining
	*/
    public final function delete(\System\Db\Database $db)
    {
		$query = new \System\Db\Query($db, SQL_ONETIMECALL_DELETE);
        $query->bind($this->getId(), \System\Db\QueryType::TYPE_INTEGER);

        $db->query($query);

        return null;
	}

	/**
	* Get the url for the onetimecall
	* @return string the url
	*/
	public final function getUrl()
	{
		return PUBLIC_ROOT . 'onetimecall/onetime/call/?token=' . $this->getToken() . '&amp;value=' . $this->getValue();
	}
}
