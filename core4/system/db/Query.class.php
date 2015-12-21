<?php
/**
* Query.class.php
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
* Contains functionality to create and set query properties
* @package \System\Db
*/
final class Query extends \System\Base\BaseObj
{
    /**
    * The sequence used to define a parameter in the query string. This is a regular string
    */
    const PARAMSTRING = '%?%';

    /**
    * @var string The SQL query in the system
    */
    protected $sql = '';
    /**
    * @var \System\Db\Database The database connection to use for the query
    */
    protected $db = null;

    /**
    * @var mixed The result type used.
    */
    protected $resultType = null;

    /**
    * @publicget
    * @publicset
    * This can be used to make use of a secondary connection pipe in the the Database, in order to decrease the amount of queries on the primary pipe.
    * This is mainly used as an optimization. Usually, the secondary pipe is a read-only pipe. Do note: transactions are not shared between pipes.
    * When the \System\Db\Database does not have a secondary pipe, it defaults back to the primary pipe.
    * @var bool When true, use the secondary connection pipe in the \System\Db\Database to query for the results, defaults to false to use the primary pipe.
    */
    protected $useSecondaryPipe = false;

    /**
    * Constructs a new query
    * @param \System\Db\Database The Database connection
    * @param string The SQL string to work with
    */
    public final function __construct(\System\Db\Database $db, $sql)
    {
        $this->db = $db;
        $this->sql = $sql;
    }

    /**
    * Returns the current resulttype. This may be a string containing a classname or a null value (default).
    * @return mixed The current resulttype
    */
    public final function getResultType()
    {
        return $this->resultType;
    }

    /**
    * Specifies the resulttype of the query. If used, and the parameter is not null, then each result row will be an instance of
    * the given class.
    * @param string The name of the class to use as the resulttype
    */
    public final function setResultType($className = null)
    {
        $this->resultType = $className;
    }

    /**
    * Mimics the str_replace function, but only replaces 1 instance.
    *
    * @param string The needle
    * @param string The replacement variable
    * @param string The haystack to replace the occurances in
    * @param int The amount of replacements made. By definition only 0 or 1
    * @return string The replacement string or the original
    */
    private function str_replace_once($needle, $replace, $haystack, &$count = 0)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false)
        {
            //no results
            $count = 0;
            return $haystack;
        }
        $count = 1;
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    /**
    * Binds a parameter to the query. This is done immediatly so, the results are not delayed.
    * Multiple parameter types can be added to the query.
    * @param string The value to be added to the query.
    * @param integer The type of parameter to be added to the query.
    * @return \System\Db\Query Returns the current instance, so we can chain.
    */
    public final function bind($parameterValue, $parameterType = \System\Db\QueryType::TYPE_INTEGER)
    {
        $count = 0;

        switch ($parameterType)
        {
            case QueryType::TYPE_INTEGER:
                if ((!ctype_digit($parameterValue)) &&
                    (!is_int($parameterValue)))
                {
                    throw new \System\Error\Exception\InvalidDatabaseQueryArgumentException('The given parameter needs to be of integer type, ' . \System\Type::getType($parameterValue) . ' given.');
                }
                $this->sql = $this->str_replace_once(self::PARAMSTRING, $this->db->sanitize($parameterValue), $this->sql, $count);
                break;
            case QueryType::TYPE_QUERY:
                $this->sql = $this->str_replace_once(self::PARAMSTRING, $this->db->sanitize($parameterValue), $this->sql, $count);
                break;
            case QueryType::TYPE_STRING:
                if (!is_string($parameterValue))
                {
                    throw new \System\Error\Exception\InvalidDatabaseQueryArgumentException('The given parameter (' . $parameterValue . ') needs to be of string type, ' . \System\Type::getType($parameterValue) . ' given.');
                }
                $this->sql = $this->str_replace_once(self::PARAMSTRING, '\'' . $this->db->sanitize($parameterValue) . '\'', $this->sql, $count);
                break;
            default:
                throw new \System\Error\Exception\InvalidDatabaseQueryArgumentException('Invalid parametertype given, ' . \System\Type::getType($parameterValue) . ' given.');
        }

        if ($count == 0)
        {
            throw new \System\Error\Exception\InvalidDatabaseQueryArgumentException('The query has no more free parameter slots: ' . $this->sql);
        }

        return $this;
    }

    /**
    * Returns the current query in the system. This function will also check if the query is correct.
    * @return string The prepared query
    */
    public final function getQuery()
    {
        if (strpos($this->sql, self::PARAMSTRING))
        {
            throw new \System\Error\Exception\InvalidDatabaseQueryArgumentException('Not all parameters have been filled in properly: ' . $this->sql);
        }
        return $this->sql;
    }
}