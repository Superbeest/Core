<?php
/**
* bootloader.php
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


//using a namespace enforces PHP 5.3 support
namespace System;

/**
* Defines the minimum PHP version needed to run this system
*/
define ('SYSTEM_MINIMUM_PHP_VERSION', '5.6.4');

/**
* We hide the exposing of PHP headers
*/
ini_set('expose_php', false);

/**
* This function checks the requirements for the system. Should not be called manually.
*/
function __requirements()
{
    \System\Version::registerRequiredConfigDirective('DATABASE_HOST');
    \System\Version::registerRequiredConfigDirective('DATABASE_USER');
    \System\Version::registerRequiredConfigDirective('DATABASE_PASS');
    \System\Version::registerRequiredConfigDirective('DATABASE_NAME');
    \System\Version::registerRequiredConfigDirective('DATABASE_PORT');
    \System\Version::registerRequiredConfigDirective('DATABASE_PERSISTANT');

    \System\Version::registerRequiredConfigDirective('PERMABAN_HOST');
    \System\Version::registerRequiredConfigDirective('PERMABAN_USER');
    \System\Version::registerRequiredConfigDirective('PERMABAN_PASS');
    \System\Version::registerRequiredConfigDirective('PERMABAN_NAME');
    \System\Version::registerRequiredConfigDirective('PERMABAN_PORT');

    \System\Version::registerRequiredConfigDirective('SITE_IDENTIFIER');
    \System\Version::registerRequiredConfigDirective('SITE_EMAIL');
    \System\Version::registerRequiredConfigDirective('SITE_NAMESPACE');

    \System\Version::registerRequiredConfigDirective('PUBLIC_ROOT');
    \System\Version::registerRequiredConfigDirective('DEFAULT_MODULES');

    \System\Version::registerRequiredConfigDirective('MINIFY_ENABLE');

    \System\Version::registerRequiredConfigDirective('AVAILABLE_LANGUAGES');
    \System\Version::registerRequiredConfigDirective('DEFAULT_LANGUAGE');

    if (version_compare(PHP_VERSION, SYSTEM_MINIMUM_PHP_VERSION) < 0)
    {
        throw new \System\Error\Exception\SystemException('The configuration is invalid: We need at least PHP ' . SYSTEM_MINIMUM_PHP_VERSION . ' in order to run.');
    }

    if ((mb_strlen(SITE_IDENTIFIER) < 3) ||
        (mb_strlen(SITE_IDENTIFIER) > 4))
    {
        throw new \System\Error\Exception\SystemException('The configuration is invalid: SITE_IDENTIFIER must be 3 or 4 chars.');
    }

    if (!defined('LUTCACHE_CACHE'))
    {
        define ('LUTCACHE_CACHE', \System\Cache\LUTCache\Types::CACHE_NONE);
    }
    else
    {
        if ((LUTCACHE_CACHE != \System\Cache\LUTCache\Types::CACHE_NONE) &&
            (LUTCACHE_CACHE != \System\Cache\LUTCache\Types::CACHE_MEMCACHE) &&
            (LUTCACHE_CACHE != \System\Cache\LUTCache\Types::CACHE_APC))
        {
            throw new \System\Error\Exception\SystemException('The configuration is invalid: LUTCACHE_CACHE is invalid.');
        }
    }

    if (!function_exists('curl_init'))
    {
        throw new \System\Error\Exception\SystemException('This system requires the cURL PHP module to be present. Please reconfigure your PHP.');
    }

    if (!class_exists('\XSLTProcessor'))
    {
        throw new \System\Error\Exception\SystemException('This system required the XSL PHP module to be present. Please reconfigure your PHP.');
    }

    //load the modules from the config file
    $defaultModules = unserialize(DEFAULT_MODULES);
    if (\System\Type::getType($defaultModules) != \System\Type::TYPE_ARRAY)
    {
        throw new \System\Error\Exception\SystemException('The configuration is invalid: DEFAULT_MODULES not set or invalid.');
    }
    foreach ($defaultModules as $defaultModule)
    {
        \System\Module\Module::load($defaultModule);
    }
}

/**
* Boots the system and reads the configuration files. Should not be called manually.
*/
function __bootloader()
{
    //get the current path
    $currentPath = getcwd();
    //we make sure the last character of the current path is a separator
    if (substr($currentPath, -1) != '/')
    {
        $currentPath .= '/';
    }

    //these definitions must be present
    if (!defined('PATH_SYSTEM'))    		{ throw new \Exception('PATH_SYSTEM is not set in paths.inc'); }
    if (!defined('PATH_CONFIG'))    		{ throw new \Exception('PATH_CONFIG is not set in paths.inc'); }
    if (!defined('PATH_TEMP'))      		{ throw new \Exception('PATH_TEMP is not set in paths.inc'); }
    if (!defined('PATH_LOGS'))      		{ throw new \Exception('PATH_LOGS is not set in paths.inc'); }
    if (!defined('PATH_MODULES'))   		{ throw new \Exception('PATH_MODULES is not set in paths.inc'); }
    if (!defined('PATH_PAGECACHE_CACHE'))   { throw new \Exception('PATH_PAGECACHE_CACHE is not set in paths.inc'); }

    //define the security locks so we can include files
    define ('InSite', null);
    define ('System', null);

    //we define the default character sets to utf8
    mb_internal_encoding("UTF-8");

    //load the autoloader. After this call, all the classes can be called.
    $autoloader = PATH_SYSTEM . 'Autoload.class.php';
    if (file_exists($autoloader))
    {
        require_once($autoloader);
    }
    else
    {
        throw new \Exception('Could not load ' . $autoloader . '. Please check the PATH_SYSTEM constant in your configuration!');
    }

    //debug parameters when the platform is our development platform
    if (\System\Server\OS::getOS() == \System\Server\OS::OS_WINDOWS)
    {
        defined('DEBUG') ||	define ('DEBUG', null);
    }

	register_shutdown_function('\System\Db\Database::handleShutdown');

    //boot the errorhandler and register the exception and error handlers
    \System\Error\ErrorHandler::getInstance();

    //set the timezone values
	defined('TIMEZONE_IDENTIFIER') || define ('TIMEZONE_IDENTIFIER', 'Europe/Amsterdam');
    \System\Version::registerRequiredConfigDirective('TIMEZONE_IDENTIFIER');
    date_default_timezone_set(TIMEZONE_IDENTIFIER);

    //register
    $register = \System\Register\Register::getInstance();

    //we set the start timer
    \System\Calendar\Timer::getSystemExecutionTime();

    //config
    require_once(PATH_CONFIG . 'site.inc');

	//initialize the language subsystem
    \System\Internationalization\Language::init();

    //initialize the system interaction system
    \System\System\Interaction\Event\SystemInteractionEvent::registerListeners();

    //register extra handlers if needed
    if (file_exists(PATH_CONFIG . 'handlers.inc'))
    {
        require_once(PATH_CONFIG . 'handlers.inc');
    }

    //turn the displaying of errors off, when we are in production environment
    defined('DEBUG') || ini_set('display_errors', 0);

    //verify the required configuration variables
    __requirements();

    //check if the visitors ip address is allowed.
    if (!\System\HTTP\Visitor\PermaBan\PermaBan::isIPAllowed(\System\HTTP\Visitor\IP::getClientIP()))
    {
        header('HTTP/1.0 403 Forbidden');
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    //database
    $register->defaultDb = \System\Db\Database::getConnection();

    //we dont want to cache our output, as this allows access without revalidating
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    //requestshizzle
    \System\Version::registerRequiredConfigDirective('DEFAULT_CONTROLLER');
    if (!defined('DEFAULT_CONTROLLER'))
    {
        throw new \System\Error\Exception\SystemException('The configuration is invalid: DEFAULT_CONTROLLER not set or invalid.');
    }
    $controller = \System\Web\Controller::callController();

    //do buffered output rendering if needed
    if ($controller->getRenderSetting() == \System\Web\Controller::RENDERER_ENABLED)
    {
        //render the surface
        $renderSurface = $controller->getRenderSurface();
        if (!$renderSurface)
        {
            throw new \System\Error\Exception\SystemException('Please make sure your controller action sets a RenderSurface!');
        }
        $controller->getRenderSurface()->execute();
    }

    //shutdown the system to prevent further execution of code
    exit();
}

//we prevent double running of the file
if (!defined('SYSTEM_BOOTLOADER'))
{
    define ('SYSTEM_BOOTLOADER', null);
    __bootloader();
}
