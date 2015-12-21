<?php
/**
* Version.class.php
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
* Version class which contains functions for the identification of the current version
* of the system framework
* @package \System
*/
class Version extends \System\Base\StaticBase
{
    /**
    * The fingerprint key used by the getFingerprint() function
    */
    const FINGERPRINT_KEY = 'MyDefaultKey';

    /**
    * The current major versionnumber
    */
    const MAJORVERSION = 4;
    /**
    * The current minor versionnumber
    */
    const MINORVERSION = 1;

    /**
    * @var \System\Collection\Vector The configurationdirectives required for a run
    */
    private static $configDirectives = null;

    /**
    * Gets the current PHP version running
    * @return string The current PHP version
    */
    public static final function getPHPVersion()
    {
        return PHP_VERSION;
    }

    /**
    * Converts the given SVN REV keyword to a integer revision number.
    * @param string The SVN REV keyword string, formatted as a regular SVN REV string.
    * @return integer The revision number
    */
    public static final function transformRevStringToInt($revision)
    {
        $matches = array();
        //filter out the nummeric value, representing the revisionnumber
        $match = preg_match('/^.*?(\d+).*?$/', $revision, $matches);
        if ($match > 0)
        {
            return intval($matches[1]);
        }
        else
        {
            return 0;
        }
    }

    /**
    * Returns the current repository revision of the system source as an integer.
    * @return int An integer representing the current revision of the system source.
    */
    public static final function getSourceRevision()
    {
        //SVN automatically replaces this text with the revisionnumber
        $revision = '$Rev: 479 $';

        return self::transformRevStringToInt($revision);
    }

    /**
    * Returns the major versionnumber
    * @return int An integer representing the current major version number.
    */
    public static final function getMajor()
    {
        return self::MAJORVERSION;
    }

    /**
    * Returns the minor versionnumber
    * @return int An integer representing the current minor version number.
    */
    public static final function getMinor()
    {
        return self::MINORVERSION;
    }

    /**
    * Registers the required configuration directive. These directives should be present in the configuration file.
    * This function doesnt check for their presence, but only serves as an informant.
    * @param string The directive that should be present
    */
    public static final function registerRequiredConfigDirective($directive)
    {
        if (self::$configDirectives == null)
        {
            self::$configDirectives = new \System\Collection\Vector();
        }

        if (!self::$configDirectives->contains($directive))
        {
            self::$configDirectives[] = $directive;

            if (!defined($directive))
            {
            	throw new \System\Error\Exception\SystemException('The configuration is invalid or incomplete: ' . $directive . ' is required and not set.');
			}
        }
    }

    /**
    * Outputs the systeminfo in a human readable format.
    * This function outputs html styled information and should only be used for displaying information.
    * @return string The html styled system information
    */
    public static final function getSystemInfo()
    {
        $output = '';

        $output .= '<table style="margin: 10px; font-family: Verdana; font-size: 12px; width: 600px; border: 1px solid black; background-color: #9999cc;">
                        <tr><td colspan="2">
                        <p style="font-size: 14px; font-weight: bold;">SYSTEM INFORMATION</p>
                        <p style="font-size: 10px;">
                            Notice: This file is copyrighted and unauthorized use is strictly prohibited.<br />
                            The file contains proprietary information and may not be distributed, modified<br />
                            or altered in any way, without written perimission.<br />
                            Copyright: SUPERHOLDER B.V. ' . date('Y') . '
                        </p>
                        </td></tr>
                    </table>';

        $info = new \System\Collection\Vector();

        $system = new \System\Collection\Map();
        $system->name = 'SYSTEM Namespace';
        $system->manifest = 'N/A';
        $system->major = self::getMajor();
        $system->minor = self::getMinor();
        $system->revision = self::getSourceRevision();
        $map = new \System\Collection\Map();
        $map->PHP = self::getPHPVersion();
        $map->hasSlowQueryListener = \System\Event\EventHandler::hasListeners('\System\Event\Event\OnSlowMySQLQueryEvent') ? 'true' : 'false';

        if (self::$configDirectives == null)
        {
            self::$configDirectives = new \System\Collection\Vector();
        }
        $map->requiredConfigDirectives = implode(', ', self::$configDirectives->getArrayCopy());

        $map->PATH_PROJECT = PATH_PROJECT;
        $map->PATH_SYSTEM = PATH_SYSTEM;
        $map->PATH_CONFIG = PATH_CONFIG;
        $map->PATH_TEMP = PATH_TEMP;
        $map->PATH_LOGS = PATH_LOGS;
        $map->PATH_MODULES = PATH_MODULES;

        $map->installedCaches = 'LUTCache, Memcache, APCCache';

        $system->additional = $map;
        $info[] = $system;

        $info->combine(\System\Module\Module::getAllModules());

        foreach ($info as $module)
        {
            $output .= '<table style="margin: 10px; font-family: Verdana; font-size: 12px; width: 600px; border: 1px solid black; background-color: #9999cc;">';
            $output .= '<tr><td colspan="2" style="text-align: center; font-weight: bold; font-size: 14px;">' . $module->name . '</td></tr>';
            $output .= '<tr><td style="background-color: #ccccff; width: 200px;">Manifest</td><td style="background-color: #cccccc; width: 400px;">' . $module->manifest . '</td></tr>';
            $output .= '<tr><td style="background-color: #ccccff; width: 200px;">Version</td><td style="background-color: #cccccc; width: 400px;">' . $module->major . "." . $module->minor . "." . $module->revision . '</td></tr>';
            foreach ($module->additional as $index=>$value)
            {
                $output .= '<tr><td style="background-color: #ccccff; width: 200px;">' . $index . '</td><td style="background-color: #cccccc; width: 400px;">' . $value . '</td></tr>';
            }

            $output .= '</table>';
        }

        return $output;
    }

    /**
    * Creates a fingerprint of the entire system and returns it as an encoded json string. This fingerprint only gives information about the system version.
    * By using this fingerprint, we can identify the versions used in the currently deployed build
    * Note that the used encryption is not a secure one.
    * @param string The key used to encode the resultset and generate the hashes. This key is required for decryption.
    * @return string The encoded fingerprint
    */
    public final static function getSystemFingerprint($encodeKey = \System\Version::FINGERPRINT_KEY)
    {
        $baseDir = PATH_SYSTEM;
        $files = \System\IO\Directory::walkDir($baseDir, new \System\Collection\Vector('php'));

        $map = new \System\Collection\Map();
        foreach ($files as $file)
        {
            $hash = new \System\Security\Hash(\System\Security\Hash::HASH_SHA512);
            $hash->addFile($file);
            $map[\System\Security\Base64Encoding::Base64Encode($file->stripBase(PATH_SYSTEM), $encodeKey)] = $hash->getHash();
        }

        $jsonObject = json_encode($map->getArrayCopy());

        $encoded = \System\Security\XOREncoding::XOREncrypt($jsonObject, $encodeKey);

        return $encoded;
    }
}
