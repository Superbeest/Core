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


namespace System\Module;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Contains module loading and management functionality.
* @package \System\Module
*/
class Module extends \System\Base\StaticBase
{
	/**
	* Normalizes the name of the module by removing the prefixing backslash and
	* adding a trailing backslash.
	* @param string The name of the module
	* @return string The normalized module name
	*/
    private static final function normalizeModuleName($moduleName)
    {
        if (mb_substr($moduleName, 0, 1) == '\\')
        {
            $moduleName = mb_substr($moduleName, 1);
        }

        if (mb_substr($moduleName, -1, 1) != '\\')
        {
            $moduleName .= '\\';
        }

        $moduleName .= 'Module';

        return $moduleName;
    }

    /**
    * Transforms the given module name to a full classname and verifies
    * the modules existence.
    * @param string The name of the module
    * @return string The full name of the class
    */
    private static final function getFullModuleName($moduleName)
    {
        $moduleName = self::normalizeModuleName($moduleName);

        if ((!class_exists($moduleName)) ||
            (!in_array('System\Module\iModule', class_implements($moduleName))) || //we cannot use instanceof, because $moduleName is not an instance
            (!is_callable(array($moduleName, 'moduleEntry'))))
        {
            throw new \System\Error\Exception\IncorrectModuleFormatException('The module is missing a proper ' . $moduleName . ' manifest file');
        }

        return $moduleName;
    }

    /**
    * Returns the register and creates a modules subnode in it, if needed.
    * @return \System\Register\Register The system register
    */
    private static final function getRegistryModuleEntry()
    {
        $register = \System\Register\Register::getInstance();
        if (!isset($register->modules))
        {
            $register->modules = new \System\Collection\Map();
        }

        return $register;
    }

    /**
    * Loads the given module into the system and executes actions defined in the manifest file.
    * Modules can only get loaded once, so calling this function multiple times is not forcing a reload of the module.
    * @param string The name of the module to load
    * @return \System\Module\iModule An instance of the module manifest file
    */
    public static final function load($moduleName)
    {
        $event = new \System\Event\Event\OnPrepareModuleLoadEvent();
        $event->setModuleName($moduleName);
        $event->raise();

        $moduleName = self::getFullModuleName($moduleName);
        $register = self::getRegistryModuleEntry();

        if (!isset($register->modules[$moduleName]))
        {
            $module = call_user_func(array($moduleName, 'moduleEntry'));
            $register->modules[$moduleName] = $module;

            //we force all the required config directives to be present
            $requiredConfigDirectives = $module->getRequiredConfigDirectives();
            foreach ($requiredConfigDirectives as $requiredConfigDirective)
            {
                if (!defined($requiredConfigDirective))
                {
                    throw new \System\Error\Exception\SystemException('Required configuration directive ' . strtoupper($requiredConfigDirective) . ' is missing for module ' . $module);
                }
            }
        }

        $event = new \System\Event\Event\OnModuleLoadedEvent();
        $event->setModuleName($moduleName);
        $event->setModule($register->modules[$moduleName]);
        $event->raise();

        return $register->modules[$moduleName];
    }

    /**
    * Returns a \System\Collection\Vector containing all the information about all the currently loaded
    * modules. This information is using a default format and displays any information from the iModule interface
    * and outputs additional information retrieval function.
    * @return \System\Collection\Vector A Vector containing a listing of all loaded modules
    */
    public static final function getAllModules()
    {
        $register = self::getRegistryModuleEntry();

        $map = new \System\Collection\Vector();

        foreach ($register->modules as $moduleName=>$module)
        {
            $mod = new \System\Collection\Map();
            $mod->name = $module->getModuleName();
            $mod->manifest = $moduleName;
            $mod->major = $module->getMajor();
            $mod->minor = $module->getMinor();
            $mod->revision = \System\Version::transformRevStringToInt($module->getSourceRevision());

            $additional = $module->getModuleInformation();
            $additional->requiredConfigDirectives = implode(', ', $module->getRequiredConfigDirectives()->getArrayCopy());
            $mod->additional = $additional;
            $map->add($mod);
        }

        return $map;
    }

    /**
    * Checks if a module is loaded.
    * @param string The name of the module. Ex.: \Module\Internationalization
    */
    public static final function isModuleLoaded($module)
    {
        $register = self::getRegistryModuleEntry();

        $moduleNormalized = self::normalizeModuleName($module);

        foreach ($register->modules as $moduleName=>$module)
        {
            if ($moduleName == $moduleNormalized)
            {
                return true;
            }
        }

        return false;
    }

    /**
    * Returns the requested functionality from the module. The module will be queried for the given functionality.
    * This is done by returning the value from the published additional map.
    * @param string The name of the module
    * @param string The name of the functionality in the additional map
    * @return mixed The value the entry in the additional map, or false if not found.
    */
    public static final function getModuleSetting($moduleName, $moduleFunctionality)
    {
        $moduleName = self::getFullModuleName($moduleName);
        $register = self::getRegistryModuleEntry();

        if (isset($register->modules[$moduleName]))
        {
            $module = $register->modules[$moduleName];
            foreach ($module->getModuleInformation() as $supportKey=>$supportValue)
            {
                if ($supportKey == $moduleFunctionality)
                {
                    return $supportValue;
                }
            }
        }

        return false;
    }
}
