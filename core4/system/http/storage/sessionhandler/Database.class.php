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


namespace System\HTTP\Storage\SessionHandler;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the database as a sessionhandler. This uses the default database connection
* @package \System\HTTP\Storage\SessionHandler
*/
final class Database extends \System\Base\BaseObj implements \SessionHandlerInterface
{
	/**
	* The table to store the sessions in
	*/
	const SESSION_TABLE = 'session';

	/**
	* @var string The name of the session
	*/
	private $sessionName = 'session';

	/**
	* @var \System\Db\Database The storage platform
	*/
	private $database = null;

	/**
	* Creates an instance of the handler and gets the default database
	*/
	public function __construct()
	{
		$this->database = \System\Db\Database::getConnection();
	}

	/**
	* Re-initialize existing session, or creates a new one. Called when a session starts or when session_start() is invoked.
	* @param string The path where to store/retrieve the session.
	* @param string The session name.
	* @return bool Always true
	*/
	public function open($savePath, $name)
	{
		$this->sessionName = $name;
		return true;
	}

	/**
	* Closes the current session. This function is automatically executed when closing the session, or explicitly via session_write_close().
	* @return bool Always true
	*/
	public function close()
	{
		return true;
	}

	/**
	* Destroys a session. Called by session_regenerate_id() (with $destroy = TRUE), session_destroy() and when session_decode() fails.
	* @param string The session ID
	*/
	public function destroy($sessionId)
	{
		$query = new \System\Db\Query($this->database, SQL_DATABASE_DESTROY);
		$query->bind(self::SESSION_TABLE, \System\Db\QueryType::TYPE_QUERY);
		$query->bind($sessionId, \System\Db\QueryType::TYPE_STRING);

		$this->database->query($query);
		return true;
	}

	/**
	* Cleans up expired sessions. Called by session_start(), based on session.gc_divisor, session.gc_probability and session.gc_lifetime settings.
	* @param int The amount of seconds to live
	* @return bool Always true
	*/
	public function gc($maxLifeTime)
	{
		$query = new \System\Db\Query($this->database, SQL_DATABASE_READ);
		$query->bind(self::SESSION_TABLE, \System\Db\QueryType::TYPE_QUERY);
		$query->bind($maxLifeTime, \System\Db\QueryType::TYPE_INTEGER);

		$this->database->query($query);

		return true;
	}

	/**
	* Reads the session data from the session storage, and returns the results. Called right after the session starts or when session_start() is called. Please note that before this method is called SessionHandlerInterface::open() is invoked.
	* @param string The sessionId
	* @return mixed The value from the session for internal processing, or an empty string
	*/
	public function read($sessionId)
	{
		$query = new \System\Db\Query($this->database, SQL_DATABASE_READ);
		$query->bind(self::SESSION_TABLE, \System\Db\QueryType::TYPE_QUERY);
		$query->bind($sessionId, \System\Db\QueryType::TYPE_STRING);

		if ($result = $this->database->querySingle($query))
		{
			return $result->data;
		}
		return '';
	}

	/**
	* Writes the session data to the session storage. Called by session_write_close(), when session_register_shutdown() fails, or during a normal shutdown.
	* @param string The session Id
	* @param mixed The encoded session data
	* @return bool Always true
	*/
	public function write($sessionId, $sessionData)
	{
		$query = new \System\Db\Query($this->database, SQL_DATABASE_WRITE);
		$query->bind(self::SESSION_TABLE, \System\Db\QueryType::TYPE_QUERY);
		$query->bind($sessionId, \System\Db\QueryType::TYPE_STRING);
		$query->bind($this->sessionName, \System\Db\QueryType::TYPE_STRING);
		$query->bind($sessionData, \System\Db\QueryType::TYPE_STRING);
		$query->bind($this->sessionName, \System\Db\QueryType::TYPE_STRING);
		$query->bind($sessionData, \System\Db\QueryType::TYPE_STRING);

		$this->database->query($query);
		return true;
	}
}