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


namespace System\Db\HTTP;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the Database over HTTP tunnels
* @package \System\Db\HTTP
*/
class Database extends \System\Db\Database
{
	const MODE_CONNECTION = 'C';
	const MODE_QUERY = 'Q';

	/**
	* @var string The tunnel url
	*/
	private $httpTunnel;

	/**
	* @var string The host
	*/
	private $databaseHost;

	/**
	* @var int The port
	*/
	private $databasePort;

	/**
	* @var string The username
	*/
	private $databaseUser;

	/**
	* @var string The password
	*/
	private $databasePassword;

	/**
	* @var string The database name
	*/
	private $databaseName;

	/**
	* Creates a connection post string.
	* @param string the mode to use for the connection
	* @return string The connection post string
	*/
	private function createPostString($mode = self::MODE_QUERY)
	{
		$params = "";
		$params .= "encodeBase64=1";
		$params .= "&host=" . $this->databaseHost;
		$params .= "&port=" . $this->databasePort;
		$params .= "&login=" . $this->databaseUser;
		$params .= "&db=" . $this->databaseName;
		$params .= "&password=" . $this->databasePassword;
		$params .= "&actn=" . $mode;

		return $params;
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
            $result = new DatabaseResult($this->httpTunnel, $this->createPostString(self::MODE_QUERY), $query, $this);

            $event = new \System\Event\Event\OnMySQLQueryEvent();
            $event->setQuery($query);
            $event->setResult($result);
            $event->raise($this);

            return $result;
        }
    }

	/**
    * The constructor of the database object. This creates the actual connection and makes the database system go in a ready state.
    * @param string The URL or IP to the database host, when prefixed with 'p:' it will be a persistant connection
    * @param string The username used to log in to the database
    * @param string The password used to log in to the database
    * @param string The name of the database to connect to
    * @param integer The port to use for the connection
    * @param string A unique string to identify the databaseconnection
    * @param string The http tunnel to use
    */
    protected function __construct($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort, $identifierString, $httpTunnel)
    {
        $this->databaseLink = null;

        $this->identifierString = $identifierString;
        $this->httpTunnel = $httpTunnel;
        $this->databaseHost = $databaseHost;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        $this->databasePort = $databasePort;
        $this->databaseName = $databaseName;
    }

    /**
    * Not supported
    * @return boolean True if the autocommit is enabled, false otherwise.
    */
    public final function getAutocommit()
    {
    	return true;
	}

	/**
    * Not supported
    * @param boolean Enable or disable the autocommit functionality
    */
    public final function setAutocommit($enable = true)
    {
	}

	/**
    * Commits the current open queries. After commit, the changes are permanent.
    * @return boolean Returns true on success, false on failure.
    */
    public final function commit()
    {
        return true;
    }

    /**
    * Does a rollback on the current open queries.
    * @return boolean Returns true on success, false on failure.
    */
    public final function rollback()
    {
        return false;
    }

    /**
    * Always returns true
    * @return bool Always true
    */
    public function ping()
    {
		return true;
	}

	/**
    * Sanitizes the given value for database insertion.
    * Note: This function should not be used to sanitize user input, nor regular variables.
    * @param string The string to sanitize
    * @return string The sanitized string
    */
	public function sanitize($value)
	{
		return addslashes($value);
		//return \System\Security\Sanitize::sanitizeString($value);
	}

	/**
	* Closing remote connections is not relevant, as it is always a new connection
	*/
	public function close()
	{
		//do nothing
	}

	/**
    * Retrieves the current database characterset.
    * @return string An empty string
    */
    public function getCharacterSet()
    {
        return "";
    }

    /**
    * Sets the current database characterset. For HTTP, no charset can be set.
    * @param string The new characterset
    */
    public function setCharacterSet($characterSet = \System\Db\Database::DEFAULT_CHARSET_ENCODING)
    {
        //do nothing
    }

	public static function getHTTPConnection($httpTunnel, $databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort)
	{
		$map = self::validateHandle();

		//we create connection object to store some information and make it easy to generate a new key
        $connectionObject = new \stdClass();
        $connectionObject->databaseHost = $databaseHost;
        $connectionObject->databaseUser = $databaseUser;
        $connectionObject->databasePassword = $databasePassword;
        $connectionObject->databaseName = $databaseName;
        $connectionObject->databasePort = $databasePort;
        $connectionObject->httpTunnel = $httpTunnel;

        $connectionString = serialize($connectionObject);

        if (!isset($map[$connectionString]))
        {
        	$map[$connectionString] = new Database($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePort, md5($connectionString), $httpTunnel);
		}

		return $map[$connectionString];
	}
}