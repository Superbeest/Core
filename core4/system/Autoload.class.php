<?php
/**
* Autoload.class.php
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


namespace System;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This line registers the autoload function to the spl
*/
spl_autoload_register(__NAMESPACE__ . '\Autoload::__autoload', true);

/**
* The Autoload container class to load the system files
* @package \System
*/
class Autoload
{
    /**
    * @var bool This variable holds whether or not the \System\Module\Module class is loaded and known.
    */
    private static $moduleSystemLoaded = false;

    /**
    * These are the supported extension by the autoloader.
    */
    private static $extensions = array(
            '.class.php',
            '.ctrl.php',
            '.service.php',
            '.struct.php',
            '.interface.php',
            '.page.php',
            '.event.php',
            '.trait.php',
            '.inc.php');

    /**
    * Hide the constructor to prevent instantiating the Autoload object
    */
    private final function __construct()
    {
    }

    private static final function checkModuleManifestLoaded(array $finalParts)
    {
        //we want to prohibit the access of modules before they are loaded.
        //but in order to do that, we need to make sure the module system is loaded.
        //if the module system is not loaded, extra modules cannot be loaded aswell, so we can safely continue in this case.
        //we dont autoload the module system, this whould cause an infinite recursive loop
        if (!self::$moduleSystemLoaded)
        {
            if (class_exists('\System\Module\Module', false))
            {
                self::$moduleSystemLoaded = true;
            }
            else
            {
                return true;
            }
        }

        //here we can check if the $class variable is in a module namespace
        if (($finalParts[0] == 'module') &&
            (mb_strtolower(end($finalParts)) != 'module'))
        {
            //$class
            $modules = \System\Module\Module::getAllModules();
            $fullClassName = implode('\\', $finalParts);
            foreach ($modules as $module)
            {
                $loadedModuleManifest = implode('\\', array_slice(explode('\\', strtolower($module->manifest)), 0, 2)) . '\\';
                $moduleNameParts = array_slice($finalParts, 0, 2);
                $moduleName = implode('\\', $moduleNameParts);
                if (strpos($fullClassName, $loadedModuleManifest) === 0)
                {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
    * This magic function loads the PHP classes.
    * This function should not be called manually, as it is called automatically by PHP.
    * @param string The name of the class to load
    */
    public static final function __autoload($class)
    {
        $class = str_replace('\\', '/', $class);

        //we want to lowercase the namespace so we can map it to a directory
        $parts = explode('/', $class);

        $finalParts = array();
        for ($x = 0; $x < count($parts) - 1; $x++)
        {
            if (strlen($parts[$x]) > 0)
            {
                $finalParts[] = strtolower($parts[$x]);
            }
        }
        $finalParts[] = $parts[count($parts) - 1];

        //we cant load the file if we do not have enough info, or the class is not in one of our namespaces
        if (count($finalParts) <= 1)
        {
            return false;
        }

        $basePath = '';
        switch (true)
        {
            case stripos($finalParts[0], 'system') === 0:
                $basePath = PATH_SYSTEM;
                break;
            case stripos($finalParts[0], 'module') === 0:
                $basePath = PATH_MODULES;
                break;
            default:
                $basePath = PATH_PROJECT;
        }

        //concatenate the lowercase folder to the file itself, but we exclude the primary folder
        $class = implode('/', array_slice($finalParts, 1));

        //we want to prohibit the access of modules before they are loaded.
        if (!self::checkModuleManifestLoaded($finalParts))
        {
            return;
        }

        foreach (self::$extensions as $extension)
        {
            $fullFile = $basePath . $class . $extension;
            if (file_exists($fullFile))
            {
                //this is one of the slowest operations in the system
                require_once($fullFile);

                //only try to load the sql file when we are a class or a struct
                if (($extension == '.class.php') ||
                    ($extension == '.struct.php'))
                {
                    //also do a sql file existence check
                    $sqlFullfile = $basePath . $class . '.sql.php';
                    if (file_exists($sqlFullfile))
                    {
                        require_once($sqlFullfile);
                    }
                }                 
                break;
            }
        }
    }
}
