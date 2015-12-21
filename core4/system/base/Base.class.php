<?php
/**
* Base.class.php
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
* This class functions as the base class for most objects
* @package \System\Base
*/
class Base extends \System\Base\BaseObj
{
    /**
    * Autodivide constructor.
    * Calls to a construct of a child of Base will be passed to separate functions
    * if the calling class contains multiple constructors following the following format:
    * __construct_<numberofparameters>
    * Naturally, when the default constructor is present in the class, this functionality is overridden.
    */
    public function __construct()
    {
        $amountOfArguments = func_num_args();

        $constructorName = '__construct_' . $amountOfArguments;

        if ((method_exists($this, $constructorName)) &&
            (is_callable(array($this, $constructorName))))
        {
            call_user_func_array(array($this, $constructorName), func_get_args());
        }
    }
}
