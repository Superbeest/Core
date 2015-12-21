<?php
/**
* Sanitize.class.php
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


namespace System\Security;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* System to sanitize the contents of given variables
* @package \System\Security
*/
class Sanitize extends \System\Base\StaticBase
{
	/**
	* Sanitizes a given value using the given parameters. This function only sanitizes strings or arrays. Other types will be returned unchanged.
	* @param mixed The value to sanitize.
	* @param boolean Encode the & (amp) sign
	* @param boolean Preserve the quotes
	* @param boolean Encode the low ASCII values (0-31)
	* @param boolean Encode the high ASCII values (128+)
	* @param boolean Remove the low ASCII values
	* @param boolean Remove the high ASCII values
	* @return mixed The sanitized value or the unchanged value
	*/
	public static final function sanitizeString($value, $encodeAmp = false, $preserveQuotes = false, $encodeLow = true, $encodeHigh = false, $stripLow = false, $stripHigh = false)
	{
		if (\System\Type::getType($value) == \System\Type::TYPE_STRING)
		{
			$flags = 0;
			if ($encodeAmp)
			{
				$flags |= FILTER_FLAG_ENCODE_AMP;
			}
			if ($preserveQuotes)
			{
				$flags |= FILTER_FLAG_NO_ENCODE_QUOTES;
			}
			if ($encodeLow)
			{
				$flags |= FILTER_FLAG_ENCODE_LOW;
			}
			if ($encodeHigh)
			{
				$flags |= FILTER_FLAG_ENCODE_HIGH;
			}
			if ($stripLow)
			{
				$flags |= FILTER_FLAG_STRIP_LOW;
			}
			if ($stripHigh)
			{
				$flags |= FILTER_FLAG_STRIP_HIGH;
			}

			//var_dump($value);
			$value = filter_var($value, FILTER_SANITIZE_STRING, $flags);
			//var_dump($value);
		}
		else if (\System\Type::getType($value) == \System\Type::TYPE_ARRAY)
		{
			foreach ($value as &$val)
			{
				$val = self::sanitizeString($val, $encodeAmp, $preserveQuotes, $encodeLow, $encodeHigh, $stripLow, $stripHigh);
			}
		}

		return $value;
	}

	/**
	* Sanitizes a given value using the given parameters. This function only sanitizes strings or arrays. Other types will be returned unchanged.
	* This function does not remove tags and only encodes the special characters.
	* @param mixed The value to sanitize.
	* @param boolean Encode the high ASCII values (128+)
	* @param boolean Remove the low ASCII values
	* @param boolean Remove the high ASCII values
	* @return mixed The sanitized value or the unchanged value
	*/
	public static final function entityString($value, $encodeHigh = true, $stripLow = false, $stripHigh = false)
	{
		if (\System\Type::getType($value) == \System\Type::TYPE_STRING)
		{
			$flags = 0;
			if ($encodeHigh)
			{
				$flags |= FILTER_FLAG_ENCODE_HIGH;
			}
			if ($stripLow)
			{
				$flags |= FILTER_FLAG_STRIP_LOW;
			}
			if ($stripHigh)
			{
				$flags |= FILTER_FLAG_STRIP_HIGH;
			}

			$value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, $flags);
		}
		else if (\System\Type::getType($value) == \System\Type::TYPE_ARRAY)
		{
			foreach ($value as &$val)
			{
				$val = self::entityString($val, $encodeHigh, $stripLow, $stripHigh);
			}
		}

		return $value;
	}

	/**
	* Converts a given string to a usuable string in urls. This removes non default characters, and
	* replaces spaces.
	* @param string The string to convert
	* @return string The urlified string
	*/
	public static final function URLify($string)
	{
		$string = html_entity_decode($string, ENT_QUOTES);
		$string = preg_replace('/[^a-zA-Z0-9 _-]/i', '', $string);
		$string = self::sanitizeString($string, false, true, false, false, true, true);
		//we do this twice to remove quotes and other character
		$string = preg_replace('/[^a-zA-Z0-9 _-]/i', '', $string);
		$string = str_replace(' ', '-', $string);
		$string = str_replace('_', '-', $string);
		$string = preg_replace('/-+/', '-', $string);
		$string = preg_replace('/^-*/', '', $string);
		$string = preg_replace('/-*$/', '', $string);

		return mb_strtolower($string);
	}
}