<?php
/**
* OnMySQLQueryEvent.class.php
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


namespace System\Event\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Event called when executing a query
* @package \System\Event\Event
*/
class OnMySQLQueryEvent extends \System\Event\EventHandler
{
    /**
    * @publicget
    * @publicset
    * @var \System\Db\Query The query that triggered this event
    */
    protected $query = null;

    /**
    * @publicget
    * @publicset
    * @var \System\Db\DatabaseResult The results for the query
    */
    protected $result = null;

    /**
    * Outputs the given query and querytime to the filelogger
    */
    public function logToFile()
    {
        $fileLogger = \System\Log\FileLogger::getInstance();
        $fileLogger->out('[' . $this->result->getQueryTime() . '] - ' . $this->query->getQuery());
    }
}