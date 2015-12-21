<?php
/**
* Timer.class.php
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


namespace System\Inspection;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Contains functionality to time the execution of specific functions.
* @package \System\Inspection
*/
class Timer extends \System\Base\StaticBase
{
    /**
    * This function measures the execution time of the given callback. It will be measured in seconds, with a precision
    * of 4 digits.
    * @param callback The callback to be called.
    * @param mixed Reference to the returnvalue container.
    * @param array An array which holds all the parameters for the callback
    * @return string The amount of time required for execution of the callback
    */
    public static final function timeCall($callback, &$returnVal, array $parameters = array())
    {
        if (is_callable($callback))
        {
            $timer = new \System\Calendar\Timer();
            $timer->start();

            $returnVal = call_user_func_array($callback, $parameters);

            $timer->stop();
            return $timer->getDuration();
        }
        else
        {
            throw new \InvalidArgumentException('callback parameter is not a valid callback');
        }
    }
}
