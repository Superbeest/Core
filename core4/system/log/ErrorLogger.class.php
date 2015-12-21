<?php
/**
* ErrorLogger.class.php
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


namespace System\Log;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements a simple error logger without error handling
* @package \System\Log
*/
class ErrorLogger extends \System\Log\BaseLogger implements \System\Log\iLogger
{
	/**
    * This functions outputs the message to the log
    * @param string The message to output to the given log.
    * @param integer The level of the logger
    */
    public function out($message, $level = \System\Log\LoggerLevel::LEVEL_INFO)
    {
    	$register = \System\Register\Register::getInstance();
    	assert($register->defaultDb);

    	$db = $register->defaultDb;

		$query = new \System\Db\Query($db, SQL_ERRORLOGGER_INSERT);
		$query->bind($level);
		$query->bind($message, \System\Db\QueryType::TYPE_STRING);
		$query->bind($message, \System\Db\QueryType::TYPE_STRING);

		$db->query($query);
	}
}