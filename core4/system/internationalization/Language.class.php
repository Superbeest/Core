<?php
/**
* language.class.php
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


namespace System\Internationalization;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the language independancy
* @package \System\Internationalization
*/
class Language extends \System\Base\StaticBase
{
    /**
    * The cookie key for language referencing
    */
    const COOKIE_KEY = 'lng';

    /**
    * @var string The default locale
    */
    private static $defaultLocale = 'en-US';
    /**
    * @var string The default localepool. It contains only 1 locale
    * @publicget
    */
    protected static $languagePool = array('en-US');
    /**
    * @var \System\Collection\Map The map with all the languages
    */
    private static $languageFiles = null;

    /**
    * @var bool Boolean to keep track if the language files are loaded.
    */
    private static $isLanguageFilesLoaded = false;

    /**
    * The replacement class in case INTL is not loaded
    */
    const LOCALE_REPLACEMENT = '\System\Internationalization\LocaleReplacement';

    /**
    * @var string The Locale class to use.
    */
    private static $localeClass = null;

    /**
    * Calls a Locale function by checking the correct library
    * @param string The functionname to call
    * @param ... Any parameters needed to call.
    * @return mixed The returnvalue of the called function
    */
    private static final function callLocaleFunction($functionName)
    {
        $args = func_get_args();
        array_shift($args);

        if (self::$localeClass == null)
        {
            if (!class_exists('\Locale'))
            {
                self::$localeClass = self::LOCALE_REPLACEMENT;
            }
            else
            {
                self::$localeClass = '\Locale';
            }
        }

        return call_user_func_array(array(self::$localeClass, $functionName), $args);
    }

    /**
    * Gets the current language for the user. This is based on the browsers request.
    * No lookups are made.
    * The language must be available in the pool, or set as default, otherwise the default is returned.
    * @return string The language to use.
    */
    public static final function getLanguage()
    {
        $langRequest = self::$defaultLocale;

        $server = new \System\HTTP\Request\Server();
        $browserRequestLang = $server->c_get('HTTP_ACCEPT_LANGUAGE');
        if (!empty($browserRequestLang))
        {
            $browserRequestLang = self::callLocaleFunction('acceptFromHttp', $browserRequestLang);
            if (self::isValidLocale($browserRequestLang))
            {
                $langRequest = $browserRequestLang;
            }
        }

        $cookie = new \System\HTTP\Storage\Cookie();
        $cookieRequestLang = $cookie->get(self::COOKIE_KEY);
        if (!empty($cookieRequestLang))
        {
            if (self::isValidLocale($cookieRequestLang))
            {
                $langRequest = $cookieRequestLang;
            }
        }

        return self::callLocaleFunction('getPrimaryLanguage', self::callLocaleFunction('lookup', self::$languagePool, $langRequest, true, self::$defaultLocale));
    }

    /**
    * Returns the language part from the given locale combination.
    * Example: input en_US returns en
    * @param string The locale combination
    * @return string The language
    */
    public static final function getPrimaryLanguage($localeCombination)
    {
        if (self::isValidLocale($localeCombination))
        {
            return self::callLocaleFunction('getPrimaryLanguage', $localeCombination);
        }

        return self::getLanguage();
    }

    /**
    * Sets the cookie language. This function does not validate the cookie setting.
    * @param string The locale to set in the cookie
    */
    public static final function setCookieLocale($locale)
    {
        $cookie = new \System\HTTP\Storage\Cookie();
        $cookie->set(self::COOKIE_KEY, $locale);
    }

    /**
    * Checks if the given localestring is a valid locale.
    * @param string The locale string to check
    * @return bool True on valid, false otherwise
    */
    public static final function isValidLocale($locale)
    {
    	if (!$locale)
    	{
    		return false;
		}
        $locale = str_replace('-', '_', $locale);
        return (self::callLocaleFunction('composeLocale', self::callLocaleFunction('parseLocale', $locale)) === $locale);
    }

    /**
    * Sets the current locale pool. The locales given are used for reference.
    * @param array The language locales to use as the pool
    * @return int The amount of languages added to the pool.
    */
    public static final function setLocalePool(array $languages)
    {
        if (count($languages) == 0)
        {
            throw new \System\Error\Exception\SystemException('A language pool must be defined. At least 1 language needs to be present');
        }

        self::$languagePool = array();

        foreach ($languages as $language)
        {
            if (self::isValidLocale($language))
            {
                self::$languagePool[] = $language;
            }
        }

        return count(self::$languagePool);
    }

    /**
    * Sets the default locale.
    * @param string The locale to use as the default
    */
    public static final function setDefaultLocale($locale)
    {
        if (self::isValidLocale($locale))
        {
            self::$defaultLocale = $locale;
            return;
        }

        throw new \InvalidArgumentException('Invalid locale given');
    }

	/**
	* Initializes the language subsystem. This function is called upon boot and is not allowed to be called
	* manually.
	*/
	public static final function init()
	{
		self::initLocalePool();
		self::initDefaultLocale();

		//we register a language function for the xslt renderer
        \System\Output\Renderer\XSLTRenderer::addRegisteredPHPFunction('\System\Internationalization\Language::getSentence');
	}

    /**
    * This function initializes the locale pool to the AVAILABLE_LANGUAGES setting in the configuration file.
    * This function is called automatically upon booting of this module, so manual calling is not needed.
    * However, it is not prohibited and can be used to reset the pool.
    */
    public static final function initLocalePool()
    {
        if (!self::setLocalePool(unserialize(AVAILABLE_LANGUAGES)))
        {
            throw new \System\Error\Exception\SystemException('Invalid default locale pool given. Please check the AVAILABLE_LANGUAGES value.');
        }
    }

    /**
    * This function initializes the default locale to the DEFAULT_LANGUAGE setting in the configuration file.
    * This function is called automatically upon booting of this module, so manual calling is not needed.
    * However, it is not prohibited and can be used to reset the default language.
    */
    public static final function initDefaultLocale()
    {
        self::setDefaultLocale(DEFAULT_LANGUAGE);
    }

    /**
    * Returns the default locale string
    * @return string The default locale string
    */
    public static final function getDefaultLocale()
    {
        return self::$defaultLocale;
    }

    /**
    * Returns the default language string
    * @return string The default language
    */
    public static final function getDefaultLanguage()
    {
        return self::callLocaleFunction('getPrimaryLanguage', self::$defaultLocale);
    }

    /**
    * Registers a language file for loading.
    * @param string The language to reference this file from. Ex: nl or en
    * @param string The (absolute) path to the language file.
    */
    public static final function registerLanguageFile($language, $file)
    {
        if (self::$isLanguageFilesLoaded)
        {
            throw new \Exception('The language files have already been loaded');
        }

        self::validateLanguageFilesMap();

        if (!isset(self::$languageFiles[$language]))
        {
            self::$languageFiles[$language] = new \System\Collection\Vector();
        }

        if (file_exists($file))
        {
            self::$languageFiles[$language][] = $file;
        }
        else
        {
            throw new \System\Error\Exception\FileNotFoundException('Could not find the given language file: ' . $file);
        }
    }

    private static final function validateLanguageFilesMap()
    {
        //initialize the languagemap
        if (self::$languageFiles == null)
        {
            self::$languageFiles = new \System\Collection\Map();
        }
    }

    private static final function loadLanguageFiles()
    {
        $language = self::getLanguage();

        if (isset(self::$languageFiles[$language]))
        {
            foreach (self::$languageFiles[$language] as $file)
            {
                if (file_exists($file))
                {
                    require_once($file);
                }
            }
        }

        self::$isLanguageFilesLoaded = true;
    }

    public static final function getSentence($sentence)
    {
        self::validateLanguageFilesMap();

        if (!self::$isLanguageFilesLoaded)
        {
            self::loadLanguageFiles();
        }

        $languageNamespace = strtoupper(self::c_getLanguage());

        //we place the Language constants in the \Language namespace
        $lookup = '\Language\\' . $languageNamespace . '\\' . strtoupper($sentence);

        if (defined($lookup))
        {
            return constant($lookup);
        }
        else
        {
            throw new \Exception('Language definition: ' . $lookup . ' is not defined in the present language files. Please load the according language file or add the definition.');
        }
    }
}
