<?php
/**
* BitStruct.class.php
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


namespace System\Base;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The base class for all bitmask structures in the system
* @package \System\Base
*/
class BitStruct extends \System\Base\BaseStruct
{
	/**
    * Returns the highest value in the structure, also calculating the bitvalue.
    * This means this function returns the highest possible value from bitwise calculations.
    * Do note that this function is slower than the BaseStruct::getHighest() function
    * @return mixed The highest value in the structure, or null
    */
    public function getHighest()
    {
		$reflectionClass = new \ReflectionClass($this);
        $reflectionConstants = $reflectionClass->getConstants();

        $highestValue = 0;
        foreach ($reflectionConstants as $constant)
        {
            $highestValue |= $constant;
        }

        return $highestValue;
    }

    /**
    * Checks if the given input $value matches against a given $matchConstant, using bitwise checking.
    * Thus returns true if value bitwise contains matchConstant. This simply reflects a binary-and check.
    * For example:
    * contains(3, VAL2): True, where VAL2 = (int)2
    * @param int The containing value
    * @param in The matchconstant to check
    * @return bool True if value contains matchConstant, false otherwise
    */
    public static final function contains($value, $matchConstant)
    {
        return (($value & $matchConstant) == $matchConstant);
    }

    /**
    * Combines the given input values using a binary or.
    * @param int The base value
    * @param int The value to combine with the base
    * @return int The combined result
    */
    public static final function combine($base, $value)
    {
        return ($base | $value);
    }

	/**
	* Removes the given value from the base set
	* @param int The base value
	* @param int The value to remove from the base
	* @return int The combined result
	*/
    public static final function remove($base, $value)
    {
    	return ($base ^ $value);
	}
}
