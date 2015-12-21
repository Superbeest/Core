<?php
/**
* Process.class.php
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


namespace System\Server;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Consolidates system process functionality
* @package \System\Server
*/
class Process extends \System\Base\StaticBase
{
    /**
    * Gracefull terminate the process.
    * @param integer The process to be killed
    * @return string The return information from the OS
    */
    public static final function terminateById($processId)
    {
        $output = '';
        switch (\System\Server\OS::getOS())
        {
            case \System\Server\OS::OS_WINDOWS:
                exec('taskkill /pid ' . $processId, $output);
                break;
            case \System\Server\OS::OS_UNIX:
                exec('kill ' . $processId, $output);
                break;
            default:
                throw new \System\Error\Exception\SystemException('Invalid OS argument given: no support for current os');
        }
        return $output;
    }

    /**
    * Forcefully terminate the process.
    * @param integer The process to be killed
    * @return string The return information from the OS
    */
    public static final function killById($processId)
    {
        $output = '';
        switch (\System\Server\OS::getOS())
        {
            case \System\Server\OS::OS_WINDOWS:
                exec('taskkill /f /pid ' . $processId, $output);
                break;
            case \System\Server\OS::OS_UNIX:
                exec('kill -9 ' . $processId, $output);
                break;
            default:
                throw new \System\Error\Exception\SystemException('Invalid OS argument given: no support for current os');
        }
        return $output;
    }

    /**
    * Returns a list with all the processes in the system, including their pId's
    * @return \System\Collection\Vector A list with the processes
    */
    public static final function getProcessList()
    {
        $output = array();
        switch (\System\Server\OS::getOS())
        {
            case \System\Server\OS::OS_WINDOWS:
                exec('tasklist', $output);
                break;
            case \System\Server\OS::OS_UNIX:
                exec('ps -A', $output);
                break;
            default:
                throw new \System\Error\Exception\SystemException('Invalid OS argument given: no support for current os');
        }

        return new \System\Collection\Vector($output);
    }
}
