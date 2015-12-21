<?php
/**
* Memory.class.php
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
* Function to profile a specific function call.
* @package \System\Inspection
*/
class Memory extends \System\Base\StaticBase
{
    /**
    * Measures the memory usage of the given callback. The difference is outputted in a map. The data returned is in bytes.
    * @param callback The callback to be called.
    * @param mixed Reference to the returnvalue container.
    * @param array An array which holds all the parameters for the callback
    * @return \System\Collection\Map A map with 4 keys: memoryUsage, realMemoryUsage, peakUsage, realPeakUsage
    */
    public static final function measureCall($callback, &$returnVal, array $parameters = array())
    {
        if (is_callable($callback))
        {
            $previousMemory = memory_get_usage();
            $previousRealMemory = memory_get_usage(true);
            $previousPeakUsage = memory_get_peak_usage();
            $previousRealPeakUsage = memory_get_peak_usage(true);

            $returnVal = call_user_func_array($callback, $parameters);

            $currentMemory = memory_get_usage();
            $currentRealMemory = memory_get_usage(true);
            $currentPeakUsage = memory_get_peak_usage();
            $currentRealPeakUsage = memory_get_peak_usage(true);

            $map = new \System\Collection\Map();
            $map->memoryUsage = $currentMemory - $previousMemory;
            $map->realMemoryUsage = $currentRealMemory - $previousRealMemory;
            $map->peakUsage = $currentPeakUsage - $previousPeakUsage;
            $map->realPeakUsage = $currentRealPeakUsage - $previousRealPeakUsage;

            return $map;
        }
        else
        {
            throw new \InvalidArgumentException('callback parameter is not a valid callback');
        }
    }
}
