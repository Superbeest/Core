<?php
/**
* LocaleReplacement.class.php
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
* This class replaces some of the functionality offered by the INTL Locale class.
* If the INTL library is not available, this class can be used as a fallback.
* @package \System\Internationalization
*/
class LocaleReplacement extends \System\Base\StaticBase
{
    /**
    * Tries to find out best available locale based on HTTP "Accept-Language" header
    *
    * @param string The header from the HTTP_AACEPT_LANGUAGE directive
    * @return string The found locale or 'en_US' on other
    */
    public static final function acceptFromHttp($browserRequestLang)
    {
        $locale = str_replace('-', '_', $browserRequestLang);

        $parts = explode('_', $locale);
        switch (count($parts))
        {
            case 1: return strtolower($parts[0]);
            case 2: return strtolower($parts[0]) . '_' . strtoupper($parts[1]);
            default: return 'en_US';
        }
    }

    /**
    * Gets the primary language from the given locale. Returns the lowercase of the first block.
    * @param string The given locale
    * @return string The language
    */
    public static final function getPrimaryLanguage($locale)
    {
        $locale = str_replace('-', '_', $locale);

        $parts = explode('_', $locale);
        return strtolower($parts[0]);
    }

    /**
    * Searches the language tag list for the best match to the language
    * @param array An array containing a list of language tags to compare to locale. Maximum 100 items allowed
    * @param string The locale to use as the language range when matching.
    * @param bool If true, the arguments will be converted to canonical form before matching.
    * @param string The locale to use if no match is found.
    * @return string The closest matching language tag or default value.
    */
    public static final function lookup(array $langtag, $locale, $cononicalize = false, $default)
    {
        if (count($langtag) == 0)
        {
            return $default;
        }
        //to make sure we have numeric indices
        $langtag = array_values($langtag);

        //make it default
        $locale = str_replace('-', '_', $locale);

        //take a first
        $shortest = $langtag[0];
        $shortestDist = \System\Math\Math::MAXINT;

        //iterate through the suggestions
        foreach ($langtag as $tag)
        {
            $tag = str_replace('-', '_', $tag);
            $dist = levenshtein($locale, $tag);
            if ($dist < $shortestDist)
            {
                $shortest = $tag;
                $shortestDist = $dist;
            }
        }
        return $shortest;
    }

    /**
    * Returns a correctly ordered and delimited locale ID
    * @param array an array containing a list of key-value pairs, where the keys identify the particular locale ID subtags, and the values are the associated subtag values.
    * return string The corresponding locale identifier
    */
    public static final function composeLocale(array $subtags)
    {
        $str = array();

        if (isset($subtags['language']))    { $str[] = $subtags['language']; }
        if (isset($subtags['script']))      { $str[] = $subtags['script']; }
        if (isset($subtags['region']))      { $str[] = $subtags['region']; }
        if (isset($subtags['variant1']))    { $str[] = $subtags['variant1']; }
        if (isset($subtags['variant2']))    { $str[] = $subtags['variant2']; }
        if (isset($subtags['private1']))    { $str[] = $subtags['private1']; }
        if (isset($subtags['private2']))    { $str[] = $subtags['private2']; }

        return implode('_', $str);
    }

    /**
    * Returns a key-value array of locale ID subtag elements
    * @param string The locale to extract the subtag array from
    * @return array Returns an array containing a list of key-value pairs, where the keys identify the particular locale ID subtags, and the values are the associated subtag values.
    */
    public static final function parseLocale($locale)
    {
        $locale = str_replace('-', '_', $locale);

        $parts = explode('_', $locale);

        $arr = array();

        if (isset($parts[0]))   { $arr['language'] = $parts[0]; }
        if (isset($parts[1]))   { $arr['script'] = $parts[1]; }
        if (isset($parts[2]))   { $arr['region'] = $parts[2]; }
        if (isset($parts[3]))   { $arr['variant1'] = $parts[3]; }
        if (isset($parts[4]))   { $arr['variant2'] = $parts[4]; }
        if (isset($parts[5]))   { $arr['private1'] = $parts[5]; }
        if (isset($parts[6]))   { $arr['private2'] = $parts[6]; }

        return $arr;
    }
}
