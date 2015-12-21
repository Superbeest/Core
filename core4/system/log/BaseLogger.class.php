<?php
/**
* BaseLogger.class.php
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
* The base class for all the loggers. This class contains common functionality
* @package \System\Log
*/
abstract class BaseLogger extends \System\Base\SingletonBase implements \System\Log\iLogger
{
    /**
    * Converts the level of a loglevel to a displayable text
    * @param integer The level of the logger
    * @return string The level of the logger in text
    */
    protected final function levelToText($errorLevel)
    {
        $level = array();

        if (LoggerLevel::contains($errorLevel, LoggerLevel::LEVEL_INFO))
        {
            $level[] = 'INFO';
        }
        if (LoggerLevel::contains($errorLevel, LoggerLevel::LEVEL_NOTICE))
        {
            $level[] = 'NOTICE';
        }
        if (LoggerLevel::contains($errorLevel, LoggerLevel::LEVEL_EVENT))
        {
            $level[] = 'EVENT';
        }
        if (LoggerLevel::contains($errorLevel, LoggerLevel::LEVEL_WARNING))
        {
            $level[] = 'WARNING';
        }
        if (LoggerLevel::contains($errorLevel, LoggerLevel::LEVEL_FAULT))
        {
            $level[] = 'FAULT';
        }
        return implode('|', $level);
    }
}
