<?php
/**
* Module.class.php
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


namespace Module\Tor;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* The module manifest file
* @package \Module\Tor
*/
final class Module extends \System\Base\Base implements \System\Module\iModule
{
    /**
    * The entry of the module. Any required initialization can be done in this function.
    * This function returns an instance of the current module.
    * @return iModule An instance of the current module
    */
    public static final function moduleEntry()
    {
        return new Module();
    }

    /**
    * Returns the current major version of the current module
    * @return integer The major version of the current module
    */
    public final function getMajor()
    {
        return 1;
    }

    /**
    * Returns the current minor version of the current module
    * @return integer The minor version of the current module
    */
    public final function getMinor()
    {
        return 0;
    }

    /**
    * Returns the current source revision. The function should contain the
    * svn rev keyword and return the modified result.
    * @return string The modified rev svn keyword string
    */
    public final function getSourceRevision()
    {
        //SVN automatically replaces this text with the revisionnumber
        return '$Rev$';
    }

    /**
    * Returns the public available name of the module. This name should be a descriptive name
    * and should be unique.
    * @return string The name of the module
    */
    public final function getModuleName()
    {
        return 'TOR';
    }

    /**
    * Returns a \System\Collection\Map with all the information the module wants to publish.
    * This information does not serve any other purpose than providing information to the enduser.
    * @return \System\Collection\Map A collection with publishable information.
    */
    public final function getModuleInformation()
    {
        $map = new \System\Collection\Map();
        return $map;
    }

	/**
    * Returns a \System\Collection\Vector with all the required configuration directives as string constants.
    * This information is only used for informative reasons and is not forced.
    * @return \System\Collection\Vector A Vector containing all the required configuration directives.
    */
    public final function getRequiredConfigDirectives()
    {
        $vec = new \System\Collection\Vector();

        return $vec;
    }
}