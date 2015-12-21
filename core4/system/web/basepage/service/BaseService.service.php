<?php
/**
* BaseService.service.php
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


namespace System\Web\BasePage\Service;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* Implements the base services
* @package \System\Web\BasePage\Service
*/
abstract class BaseService extends \System\Web\Service
{
	/**
	* Iterates through the given json object and process each item.
	* Returns true when ALL json requests are succesfull processed. Does not abort the chain if a request fails.
	* Also sets the success property in the serviceresult and defines responses as an array in the serviceresult.
	* @param string The name of the method in the current static context.
	* @param string The source context. Usually a post variable or the REST variable.
	* @param \System\Collection\Map The used serivceResult Map.
	* @param \System\Db\Database The default database to use
	* @return bool True of all the messages are processed correctly, false if even one fails
	*/
	protected static final function processJSONCallback($method, $sourceVariable, \System\Collection\Map $serviceResult, \System\Db\Database $defaultDb)
	{
		$isOk = true;
		$serviceResult->success = true;
		$serviceResult->responses = array();

		if (method_exists(static::getStaticClassName(), $method))
		{
			foreach (self::getJSONObjectVector($sourceVariable) as $jsonObject)
			{
				if (!static::$method($serviceResult, $defaultDb, $jsonObject))
				{
					$isOk = false;
					$serviceResult->success = false;
				}
			}
		}
		else
		{
			return new \System\Error\Exception\MethodDoesNotExistsException('The given method ' . $method . ' does not exist in the local context ' . static::getStaticClassName());
		}

		return $isOk;
	}

	/**
	* Decodes the given content and converts it to a JSON object.
	* The JSON object(s) will be placed inside a Vector for easy handling.
	* @param string The content string to work with
	* @return \System\Collection\Vector A vector with JSON objects
	*/
	private static final function getJSONObjectVector($variable)
	{
		if ($variable)
		{
			$json = json_decode(html_entity_decode($variable), false);

			if (\System\Type::getType($json) == \System\Type::TYPE_NULL)
			{
				throw new \System\Error\Exception\NullPointerException('Could not decode the given json string: ' . $variable);
			}

			if (\System\Type::getType($json) == \System\Type::TYPE_ARRAY)
			{
				return new \System\Collection\Vector($json);
			}

			$vec = new \System\Collection\Vector();
			$vec[] = $json;

			return $vec;
		}

		return new \System\Collection\Vector();
	}
}