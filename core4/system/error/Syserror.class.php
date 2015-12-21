<?php
/**
* Syserror.class.php
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


namespace System\Error;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements a syserror log entry
* @package \System\Error
*/
class Syserror extends \System\Base\DynamicBaseObj
{
    /**
    * Returns the full path to the XML file for the object
    * @return string The full path to the XML tree file
    */
    public static final function getXMLSourceFile()
    {
        return PATH_SYSTEM . 'error\syserror.xml';
    }

	/**
	* Removes the current log entry
	* @param \System\Db\Database The database to operate on
	*/
	public final function delete()
	{
		$query = new \System\Db\Query($this->getDatabase(), SQL_LOG_SYSERROR_DELETE);
		$query->bind($this->getId(), \System\Db\QueryType::TYPE_INTEGER);

		$this->getDatabase()->query($query);

		return true;
	}

	/**
	* Outputs the code property
	* @return string The code
	*/
	public final function getCode()
	{
		$code = $this->internal('getCode');

		return \System\Error\ErrorHandler::translateErrorNumber($code);
	}

	/**
	* Gets the amount of total log entries
	* @param \System\Db\Database The database to query
	* @return int The amount of items in the log
	*/
	public static final function getAmount(\System\Db\Database $db)
	{
		$query = new \System\Db\Query($db, SQL_SYSERROR_TOTALCOUNT);
		$results = $db->queryScalar($query);

		assert($results->hasItems());

		return $results->first();
	}
}