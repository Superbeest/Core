<?php
/**
* Object.class.php
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


namespace System\Db;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* Provides functionality to use our O/R layer
* @package \System\Db
*/
class Object extends \System\Base\StaticBase
{
	/**
	* Dynamically loads objects from the relational database into xml based objects.
	* The object used must inherit from the \System\Base\DynamicBaseObj class.
	* @param \System\Db\Database The database object to query
	* @param string The full classname of the class to use as the base object.
	* @param mixed The parameters to be used as the primary key.
	* @param bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.
	* @param bool When true, the secondary db connection pipe is used to read the data from.
	* @return mixed If there is one result, then only an instance of the requested object is returned; when there are multiple results a \System\Db\DatabaseResult vector is returned, also see the $alwaysUseContainer parameter. If there are no results, null is returned
	* @see \System\Db\Object::load();
	*/
	public static final function loadPrimary(\System\Db\Database $db, $className, $primarykeyValue, $alwaysUseContainer = false, $useSecondaryDatabasePipe = true)
	{
		return self::load($db, $className, \System\Base\DynamicBaseObj::PRIMARYKEY_CONDITION_NAME_LOAD, new \System\Collection\Vector($primarykeyValue), $alwaysUseContainer, $useSecondaryDatabasePipe);
	}

	/**
	* Dynamically loads objects from the relational database into xml based objects.
	* The object used must inherit from the \System\Base\DynamicBaseObj class.
	* @param \System\Db\Database The database object to query
	* @param string The full classname of the class to use as the base object.
	* @param string The condition string as described in the objects xml file
	* @param \System\Collection\Vector The parameters to be used in the condition. If an item in the vector is a vector itself, its items are imploded by ', ' and treated as a \System\Db\QueryType::TYPE_QUERY parameter
	* @param bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.
	* @param bool When true, the secondary db connection pipe is used to read the data from.
	* @return mixed If there is one result, then only an instance of the requested object is returned; when there are multiple results a \System\Db\DatabaseResult vector is returned, also see the $alwaysUseContainer parameter. If there are no results, null is returned
	*/
	public static final function load(\System\Db\Database $db, $className, $condition, \System\Collection\Vector $parameters = null, $alwaysUseContainer = false, $useSecondaryDatabasePipe = true)
	{
		$event = new \System\Event\Event\OnBeforeDynamicObjectLoadEvent();
		$event->setDatabase($db)
			->setDynamicClassName($className)
			->setCondition($condition)
			->setParameters($parameters ?: new \System\Collection\Vector()) //we dont want to send null as the function does not accept that
			->setAlwaysUseContainer($alwaysUseContainer)
			->setUseSecondaryDatabasePipe($useSecondaryDatabasePipe);
		$event->raise();

		//if the event has listeners that want to override the call, we redirect it and call that instead, this will logically re-fire the event!
		if (($event->hasListeners('\System\Event\Event\OnBeforeDynamicObjectLoadEvent')) &&
			(
				($event->getDatabase() != $db) ||
				($event->getDynamicClassName() != $className) ||
				($event->getCondition() != $condition) ||
				(!(((!$parameters) && ($event->getParameters()->count() == 0)) || ($parameters == $event->getParameters()))) || //check if the parameters are equal because the can be null as input and the set() functions cannot accept null parameters
				($event->getAlwaysUseContainer() != $alwaysUseContainer) ||
				($event->getUseSecondaryDatabasePipe() != $useSecondaryDatabasePipe)
			))
		{
			return self::load($event->getDatabase(), $event->getDynamicClassName(), $event->getCondition(), $event->getParameters(), $event->getAlwaysUseContainer(), $event->getUseSecondaryDatabasePipe());
		}

		if ((!class_exists($className)) ||
			(!is_subclass_of($className, '\System\Base\DynamicBaseObj')))
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException('The given class ' . $className . ' does not appear to be a valid child of \System\Base\DynamicBaseObj or does not exist (is the Module loaded?).');
		}

		call_user_func(array($className, 'prepareObject'));

		$queryString = call_user_func(array($className, 'queryForCondition'), $condition);
		$query = new \System\Db\Query($db, $queryString);
		$query->setResultType($className);

		//Use or dont use the secondary connection pipe
		$query->setUseSecondaryPipe($useSecondaryDatabasePipe);

		/*
		we need the validator to check the type of the value.
		this is needed, because an integer can also be string containing numbers.
		*/
		$val = new \System\Security\Validate();
		if ($parameters)
		{
			foreach ($parameters as $index=>$param)
			{
				//we need to decide the type of the parameter. Currently we only support integers and strings and Vectors.
				$type = \System\Db\QueryType::TYPE_INTEGER;
				//if the item is a Vector, we implode the vector and add it as a \System\Db\QueryType::TYPE_QUERY type parameter.
				if ($param instanceof \System\Collection\Vector)
				{
					$type = \System\Db\QueryType::TYPE_QUERY;
					$param = $param->convertToString();
				}
				else if ($val->isInt($param, $index, null, null, true) == \System\Security\ValidateResult::VALIDATE_INVALIDVALUE)
				{
					$type = \System\Db\QueryType::TYPE_STRING;
				}

				$query->bind($param, $type);
			}
		}

		//actually execute the query
		$results = $db->query($query);

		//if there is only 1 result, then we just return that instead of the entire Vector
		if (($results->count() == 1) &&
			(!$alwaysUseContainer))
		{
			return $results->current();
		}

		//we return null if there are no results
		if (($results->count() == 0) &&
			(!$alwaysUseContainer))
		{
			return null;
		}

		return $results;
	}
}