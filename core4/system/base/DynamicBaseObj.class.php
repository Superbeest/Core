<?php
/**
* DynamicBaseObj.class.php
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


namespace System\Base;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Provides functionality for dynamic objects
* @package \System\Base
*/
abstract class DynamicBaseObj extends \System\Base\BaseObj implements iDynamicBaseObj
{
	/**
	* This name is used as the name for the primary key condition (loading)
	*/
	const PRIMARYKEY_CONDITION_NAME_LOAD = '__primary_key_load';
	/**
	* This name is used as the name for the primary key condition (saving)
	*/
	const PRIMARYKEY_CONDITION_NAME_STORE = '__primary_key_store';

	/**
	* Defines Base64 encryption
	*/
	const ENCRYPTION_BASE64 = 'base64';
	/**
	* Defines XOR encryption
	*/
	const ENCRYPTION_XOR = 'xor';
	/**
	* Defines AES encryption
	*/
	const ENCRYPTION_AES = 'aes';

	/**
	* The key to use for encryption
	*/
	const ENCRYPTION_KEY = 'RandomKey';

	/**
	* @var \System\Collection\Vector This Vector contains all the field changes in the object
	*/
	private $modifications = null;

	/**
	* @var \System\Collection\Vector This Vector contains all the changes in the virtual fields
	*/
	private $virtualModifications = null;

	/**
	* @var \System\Collection\Map All the retrieved data is stored in this map
	*/
	private $data = null;

	/**
	* This map contains all the queries for all the objects in the system as it is static
	* @var \System\Collection\Map The map containing all the queries
	*/
	private static $queryMap = null;

	/**
	* This map contains all the xml trees for all the objects in the system as it is static
	* @var \System\Collection\Map The Map containing all the SimpleXMLElement structure trees
	*/
	private static $xmlTree = null;

	/**
	* This map contains all the virtuals for all the objects in the system as it is static
	* @var \System\Collection\Map The map containing all the virtual information
	*/
	private static $virtualMap = null;

	/**
	* This map contains all the fields for all the objects in the system as it is static.
	* @var \System\Collection\Map The map containing all field information
	*/
	private static $fieldMap = null;

	/**
	* This map contains all the conditions for all the objects in the system as it is static.
	* @var \System\Collection\Map The map containing all condition information
	*/
	private static $conditionMap = null;

	/**
	* @var \System\Db\Database The database from wich the object is loaded
	*/
	private $database = null;

	/**
	* Creates the object and sets the database.
	* @param \System\Db\Database The database from where the object is loaded
	*/
	protected final function __construct(\System\Db\Database $db)
	{
		$this->database = $db;
	}

	/**
	* The database from wich the object is loaded
	* @return \System\Db\Database The database from wich the object is loaded
	*/
	public final function getDatabase()
	{
		return $this->database;
	}

	/**
	* Calls internally defined functions. By using this function, one can override the default functionnames
	* and still retain access to dynamic functions.
	* @param string The name of the function, ex: 'getId'
	* @param array The arguments passed to the function, each argument in an entry.
	* @return mixed Setters return the current object, getters return the results
	*/
	protected final function internal($functionName, array $arguments = array())
	{
		return $this->__call($functionName, $arguments);
	}

	/**
	* Sets the field if it exists. Currently it can only set normal fields. Virtual fields are kinda bullshitty. If you want to set a
	* virtual field, then you should add the keys for that as regular fields.
	* @param string The name of the called method
	* @param string The name of the field to retrieve, lowcaps
	* @param array The arguments given to the function call
	*/
	private final function setField($name, $fieldName, array $arguments)
	{
		$fieldMap = self::validateMap(self::$fieldMap);
		$dataMap = $this->validateInstanceMap($this->data);
		$modifications = $this->validateInstanceMap($this->modifications);
		$virtualModifications = $this->validateInstanceMap($this->virtualModifications);
		$virtualMap = self::validateMap(self::$virtualMap);

		if ($fieldMap->keyExists($fieldName))
		{
			//we get the field data here, so we can test all properties
			$fieldData = $fieldMap->$fieldName;

			//check if the field has an access modifier and adhere to that
			if ((isset($fieldData['noset'])) &&
				(mb_strtolower((string)$fieldData['noset']) == 'true'))
			{
				throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not exist in this context.');
			}

			//we only support 1 argument, as a property can only have one value.
			if (count($arguments) == 1)
			{
				//if the given value equals the nullify attribute, we store that value in the object without further checking
				if ((isset($fieldData['nullify'])) &&
					((string)$fieldData['nullify'] == $arguments[0]))
				{
					$value = (string)$fieldData['nullify'];
				}
				else
				{
					switch (mb_strtolower((string)$fieldData['type']))
					{
						case \System\Type::TYPE_TIMESTAMP:
							if (!($arguments[0] instanceof \System\Calendar\Time))
							{
								throw new \System\Error\Exception\InvalidArgumentException('The given argument is not a valid \System\Calendar\Time object');
							}
							$value = $arguments[0]->toYYYYMMDDHHMMSS();
							break;
						case \System\Type::TYPE_SERIALIZED: //serialized is deprecated
						case \System\Type::TYPE_ARRAY:
							$value = self::encodeArray($arguments[0]);
							break;
						case \System\Type::TYPE_BOOL:
						case \System\Type::TYPE_BOOLEAN:
							if (is_bool($arguments[0]) === false)
							{
								throw new \System\Error\Exception\InvalidArgumentException('The given argument is not a boolean value');
							}
							$value = intval($arguments[0]);
							break;
						default:
							$value = $arguments[0];
					}
				}
				$dataMap->$fieldName = $value;

				//we store the fields that are modified, so we can easily store them
				if (!$modifications->contains($fieldName))
				{
					$modifications[] = $fieldName;
				}
			}
			else
			{
				throw new \InvalidArgumentException('Method ' . $this->getClassName() . '->' . $name . '() expects 1 parameter.');
			}
		}
		//check if the given fieldname is a virtual
		elseif ($virtualMap->keyExists($fieldName))
		{
			//we get the field data here, so we can test all properties
			/** @var \SimpleXMLElement */
			$virtualMethod = $virtualMap->$fieldName;
			$expectedType = (string)$virtualMethod['type'];
			//is there a multiset retrieval param? this is not supported as we only expect 1 entry as a parameter
			$alwaysUseContainer = (isset($virtualMethod['alwaysusecontainer'])) &&
									((mb_strtolower((string)$virtualMethod['alwaysusecontainer']) == 'true') || (mb_strtolower((string)$virtualMethod['alwaysusecontainer']) == 'yes'));

			//now we do some prerequisite checking.
			if ((!$alwaysUseContainer) &&
				(isset($virtualMethod['setmethod'])) && //we need the setmethod property
				($virtualMethod->value->count() == 1)) //only one parameter is allowed
			{
				//first check the parameter count
				if ((count($arguments) != 1) ||
					(!$arguments[0] instanceof $expectedType))
				{
					throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not accept the given parameters. Expected: ' . $this->getClassName() . '->' . $name . '(' . $expectedType . ').');
				}

				//retrieve the value given in the setmethod property. This will be called on the given argument from the required type
				$call = (string)$virtualMethod['setmethod'];
				$newValue = $arguments[0]->$call();

				//this is the value entered in the <value> tag of the virtual.
				$valueParameter = (string)$virtualMethod->value;

				//we need to search for corresponding fields in the current model and update those too using their own api
				foreach ($fieldMap as $fName=>$fMap)
				{
					if ((isset($fMap['dbkey'])) &&
						(mb_strtolower($fMap['dbkey']) == mb_strtolower($valueParameter)))
					{
						$deferredSetter = 'set' . ucfirst($fName);
						$this->$deferredSetter($newValue);
					}
				}

				//create the virtual queryField and store that value
				$queryField = 'virtual_' . $valueParameter;
				$dataMap->$queryField = $newValue;

				//we store the $valueParameter variable, because that is faster to lookup and still contains the same level of abstraction
				if (!$virtualModifications->contains($valueParameter))
				{
					$virtualModifications[] = $valueParameter;
				}
			}
			else
			{
				throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() is not compatible to be used as a virtual setter.');
			}
		}
		else
		{
			throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not exist in this context or is a virtual function.');
		}
	}

	/**
	* Retrieves the field from the object and returns the desired field, if exists.
	* @param string The name of the called method
	* @param string The name of the field to retrieve
	* @param array The arguments given to the function call
	* @return mixed The requested field
	*/
	private final function retrieveField($name, $fieldName, array $arguments)
	{
		$fieldMap = self::validateMap(self::$fieldMap);
		$virtualMap = self::validateMap(self::$virtualMap);
		$dataMap = $this->validateInstanceMap($this->data);

		//check for the existance of a property that adheres to the given fieldName
		if ($fieldMap->keyExists($fieldName))
		{
			$fieldData = $fieldMap->$fieldName;

			//first check if we are allowed to access this field
			if ((isset($fieldData['noget'])) &&
				(mb_strtolower((string)$fieldData['noget']) == 'true'))
			{
				throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not exist in this context.');
			}

			//we retrieve the data from the object
			$value = $dataMap->$fieldName;

			//if the internal data equals the optional nullify property, we pretend the value to be an empty string, thus uninitialized.
			if ((isset($fieldData['nullify'])) &&
				((string)$fieldData['nullify'] == $value))
			{
				$value = '';
			}

			//we interpret our data as the given type
			switch (mb_strtolower((string)$fieldData['type']))
			{
				case \System\Type::TYPE_TIMESTAMP:
					return \System\Calendar\Time::fromMySQLTimestamp($value);
				case \System\Type::TYPE_BOOL:
				case \System\Type::TYPE_BOOLEAN:
					return (bool)$value;
				case \System\Type::TYPE_SERIALIZED: //this type is deprecated and we try to convert current existing fields to array type
				case \System\Type::TYPE_ARRAY:
					return self::decodeArray($value);
				case \System\Type::TYPE_INT:
				case \System\Type::TYPE_INTEGER:
					return (int)$value;
				default:
					return $value;
			}
		}
		//check if the given fieldName is a virtual function
		elseif ($virtualMap->keyExists($fieldName))
		{
			//we can use different databases, but default to one used by this instance
			$db = $this->getDatabase();

			//first check the parameter count
			if ((count($arguments) > 1) ||
				((count($arguments) == 1) && (!$arguments[0] instanceof \System\Db\Database)))
			{
				throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not accept the given parameters. Expected: ' . $this->getClassName() . '->' . $name . '(\System\Db\Database) or ' . $this->getClassName() . '->' . $name . '().');
			}

			//check if there is an optional database given to use for the virtual function
			if ((count($arguments) == 1) &&
				($arguments[0] instanceof \System\Db\Database))
			{
				$db = $arguments[0];
			}

			$virtualMethod = $virtualMap->$fieldName;
			$parameters = new \System\Collection\Vector();
			//virtuals only have <value> nodes as children
			foreach ($virtualMethod->children() as $parameterElement)
			{
				//during the creation of the object we included these fields, so we dont need to check their existence in the field list; we know they exist.
				$queryField = 'virtual_' . (string)$parameterElement;
				$parameters->add($dataMap[$queryField]);
			}

			//do we want the result as a set?
			$alwaysUseContainer = (isset($virtualMethod['alwaysusecontainer'])) &&
									((mb_strtolower((string)$virtualMethod['alwaysusecontainer']) == 'true') || (mb_strtolower((string)$virtualMethod['alwaysusecontainer']) == 'yes'));

			return \System\Db\Object::load($db, (string)$virtualMethod['type'], (string)$virtualMethod['condition'], $parameters, $alwaysUseContainer);
		}
		else
		{
			throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not exist in this context.');
		}
	}

	/**
	* This function defines how the properties should be accessed and set from outside.
	* Should not be called directly. Use internal() instead.
	* It actually makes the public API defined in the xml work.
	* This function retains the ability to use the default call handlers.
	* @param string The name of the function getting called
	* @param array The arguments for this function
	* @return mixed Setters return the current object, getters return the results
	*/
	public final function __call($name, array $arguments)
	{
		//we check the special calls and try to execute those (this works recursive).
		$returnVal = false;
		if (self::__callDefaultHandlers($name, $arguments, $this, $returnVal))
		{
			return $returnVal;
		}

		$call = mb_strtolower($name);

		switch (true)
		{
			case (mb_substr($call, 0, 3) == 'get'):
				$fieldName = mb_substr($call, 3);
				return $this->retrieveField($name, $fieldName, $arguments);
			case (mb_substr($call, 0, 2) == 'is'):
				$fieldName = mb_substr($call, 2);
				return (bool)$this->retrieveField($name, $fieldName, $arguments);
			case (mb_substr($call, 0, 3) == 'set'):
				$fieldName = mb_substr($call, 3);
				$this->setField($name, $fieldName, $arguments);
				return $this;
			default:
				throw new \System\Error\Exception\InvalidMethodException('Method ' . $this->getClassName() . '->' . $name . '() does not exist in this context.');
		}

		throw new \System\Error\Exception\MethodDoesNotExistsException('Method ' . $this->getClassName() . '->' . $name . '() does not exists in the current context');
	}

	/**
    * Adds support for specific function call methods and adds extra loader syntactic sugar
    * By overloading this function new functionality can be added.
    * This function makes use of the __callStaticDefaultHandlers function.
    * This function is a built in PHP magic function and should not be called directly.
    * @param string The name of the function to call
    * @param array  An array containing all the arguments
    * @return mixed The return value of the called function
    * @see __call()
    */
	public static final function __callStatic($name, array $arguments)
	{
		//we check the special calls and try to execute those (this works recursive).
		$returnVal = false;
        if (self::__callDefaultHandlers($name, $arguments, get_called_class(), $returnVal))
        {
            return $returnVal;
        }

		//make all lowcaps for easy parsing
        $call = mb_strtolower($name);

        switch (true)
		{
			case mb_substr($call, 0, 4) == 'load':
				$condition = mb_substr($name, 4);

				//convert all caps to _<cap>
				$condition = preg_replace('/([A-Z])/', '_$1', $condition);
				$condition = $condition{0} == '_' ? mb_substr($condition, 1) : $condition;



				//we need the condition in lower caps
				$condition = mb_strtolower($condition);

				$argumentCount = count($arguments);

				if (($argumentCount >= 1) && //we need at least the database param
					($arguments[0] instanceof \System\Db\Database))
				{
					$lastArgument = $arguments[$argumentCount - 1];
					//we support the option to only have 1 db param, so check array bounds
					$beforeLastArgument = ($argumentCount >= 2) ? $arguments[$argumentCount - 2] : null;

					$db = array_shift($arguments);

					//set the initial values to the same defaults as the load() api
					$alwaysUseContainer = false;
					$useSecondaryPipe = true;

					switch (true)
					{
						case ((is_bool($lastArgument)) &&
							  (is_bool($beforeLastArgument))):
							$useSecondaryPipe = array_pop($arguments);
							$alwaysUseContainer = array_pop($arguments);

							break;
						case (is_bool($lastArgument)):
							$alwaysUseContainer = array_pop($arguments);
							break;
						default:
							//ignore case
					}

					$paramVec = new \System\Collection\Vector();
					//we iterate this, so we can use vectors as parameters
					foreach ($arguments as $argument)
					{
						$paramVec[] = $argument;
					}

					return self::load($db, $condition, $paramVec, $alwaysUseContainer, $useSecondaryPipe);
				}
				break;
			default:
				throw new \System\Error\Exception\MethodDoesNotExistsException('Method ' . get_called_class() . '::' . $name . '() does not exists in the current context');
		}

        throw new \System\Error\Exception\MethodDoesNotExistsException('Method ' . get_called_class() . '::' . $name . '() does not exists in the current context');
	}

	/**
	* Creates the virtual map with the proper properties so we can use this map as a resource for the __call function.
	* This function only looks at the virtuals node in the XML tree, if this node is not present, it exits.
	* @param \SimpleXMLElement The XML tree to parse
	*/
	private static final function createVirtuals(\SimpleXMLElement $xml)
	{
		$map = self::validateMap(self::$virtualMap);

		if (isset($xml->virtuals))
		{
			foreach ($xml->virtuals->children() as $methodName=>$virtualMethod)
			{
				if ((isset($virtualMethod['type'])) &&
					(isset($virtualMethod['condition'])) &&
					(count($virtualMethod->children()) > 0))
				{
					$map[mb_strtolower($methodName)] = $virtualMethod;
				}
			}
		}
	}

	/**
	* Creates the field map with all the properties so we can use this map as a resource for the __call function.
	* This function only looks at the fields node in the XML tree, if this node is not present, it exits.
	* @param \SimpleXMLElement The XML tree to parse
	*/
	private static final function createFields(\SimpleXMLElement $xml)
	{
		$map = self::validateMap(self::$fieldMap);

		if (isset($xml->fields))
		{
			foreach ($xml->fields->children() as $fieldName=>$fieldData)
			{
				if ((isset($fieldData['type'])) &&
					(isset($fieldData['dbkey'])))
				{
					//validate the type attributes
					switch (mb_strtolower((string)$fieldData['type']))
					{
						case \System\Type::TYPE_ARRAY:
						case \System\Type::TYPE_BOOL:
						case \System\Type::TYPE_BOOLEAN:
						case \System\Type::TYPE_SERIALIZED:
						case \System\Type::TYPE_TIMESTAMP:
						case \System\Type::TYPE_STRING:
						case \System\Type::TYPE_INT:
						case \System\Type::TYPE_INTEGER:
							$map[mb_strtolower($fieldName)] = $fieldData;
							break;
						default:
							throw new \Exception('Invalid type for ' . (string)$fieldName . ': ' . mb_strtolower((string)$fieldData['type']) . '. Perhaps this field should be virtual?');
					}

					//validate the encoding attributes
					if (isset($fieldData['encoding']))
					{
						switch (mb_strtolower((string)$fieldData['encoding']))
						{
							case self::ENCRYPTION_BASE64:
							case self::ENCRYPTION_XOR:
							case self::ENCRYPTION_AES:
								//these encodings we accept as valid encoding values
								break;
							default:
								throw new \System\Error\Exception\ObjectLoaderSourceException('Invalid encoding for ' . (string)$fieldName . '. The given encryptionmethod is not supported.');
						}
					}
				}
				else
				{
					throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing type or dbkey attribute in ' . (string)$fieldName);
				}
			}
		}
	}

	/**
	* Creates the queries for the conditions.
	* This function also appends to the virtuals map and the access map, based on the information retrieved from
	* building the queries.
	* @param \SimpleXMLElement The xml tree to traverse.
	*/
	private static final function createQueries(\SimpleXMLElement $xml)
	{
		$map = self::validateMap(self::$queryMap);
		$conditionMap = self::validateMap(self::$conditionMap);

		//load the table definitions from the xml
		$querySources = self::createQuerySources($xml);

		$selectors = array();
		self::createQuerySelectors($xml, $selectors);
		self::appendVirtualSelectors($selectors);

		//build the first components of the query
		$query = 'SELECT ' . implode(', ', $selectors) . ' FROM ' . $querySources;

		if (isset($xml->conditions))
		{
			foreach ($xml->conditions->children() as $condition)
			{
				//we copy the query to get a fresh one for each iteration
				$queryCopy = $query;

				//if this argument is set to true or yes, we want to calculate the total amount of rows, as if we ignored the limit
				if ((isset($condition['calculatetotals'])) &&
					((strtolower($condition['calculatetotals']) == 'yes') || (strtolower($condition['calculatetotals']) == 'true')))
				{
					$queryCopy = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $queryCopy);
				}

				//the name of the condition needs to be present
				if (isset($condition['name']))
				{
					//parse the condition parts
					$where = '';
					$groupBy = '';
					$orderBy = '';
					$limit = '';

					foreach ($condition->children() as $partType=>$queryPart)
					{
						switch (mb_strtolower($partType))
						{
							case 'where':
								$where = (string)$queryPart;
								break;
							case 'groupby':
								$groupBy = (string)$queryPart;
								break;
							case 'orderby':
								$orderBy = (string)$queryPart;
								break;
							case 'limit':
								$limit = (string)$queryPart;
								break;
							default:
								throw new \System\Error\Exception\DatabaseQueryException('Invalid condition subtype given: ' . $partType);
						}
					}
					//convert the conditionparts to a full query
					$conditionQuery = '';
					if (!empty($where))
					{
						$conditionQuery .= 'WHERE ' . $where;
					}
					if (!empty($groupBy))
					{
						$conditionQuery .= ' GROUP BY ' . $groupBy;
					}
					if (!empty($orderBy))
					{
						$conditionQuery .= ' ORDER BY ' . $orderBy;
					}
					if (!empty($limit))
					{
						$conditionQuery .= ' LIMIT ' . $limit;
					}
					$conditionQuery = trim($conditionQuery);
					$fullQuery = $conditionQuery;

					//only add it, if there actually is a condition.
					if (mb_strlen($conditionQuery) > 0)
					{
						$fullQuery = $queryCopy . ' ' . $conditionQuery;
					}
					$key = (string)$condition['name'];
					$map[$key] = htmlentities($fullQuery);
					$conditionMap[$key] = htmlentities($conditionQuery);
				}
			}
		}
	}

	/**
	* This function is used to append the parameters used in the virtual functions to the selectors.
	* All of the parameters for the virtual functions are registered as nonget/nonset, so they cannot be accessed from the outside.
	* @param array The selectors by reference.
	* @param \System\Collection\Map The current set of virtual functions.
	* @param \System\Collection\Map The access modifiers.
	*/
	private static final function appendVirtualSelectors(array &$selectors)
	{
		$virtualsMap = self::validateMap(self::$virtualMap);
		foreach ($virtualsMap as $virtualFunction)
		{
			foreach ($virtualFunction->children() as $valueNode)
			{
				$source = "`" . (string)$valueNode . "`";
				$field = mb_strtolower('virtual_' . (string)$valueNode);
				$selector = $source . ' AS `' . $field . '`';

				//if another virtual function already needs this field, we dont add it again.
				if (!in_array($selector, $selectors))
				{
					$selectors[] = $selector;
				}
			}
		}
	}

	/**
	* This function only needs the be called once every run to load and to parse the XML file
	* This call is made automatically. Successive calls will instantly return.
	*/
	public static final function prepareObject()
	{
		//initialize the xmltree map for the first time in the system
		//all the objects in the system use the same map as it is static
		//we do this inline so we can check for the existance of the entry itself instead of using validateMap()
		if (self::$xmlTree == null)
		{
			self::$xmlTree = new \System\Collection\Map();
		}

		$key = get_called_class();
		if (!isset(self::$xmlTree[$key]))
		{
			//we get the xml file using late static binding
			$xmlFile = \System\IO\Directory::getPath(static::getXMLSourceFile());

			//parse the xml file and create an xml tree
			$xml = self::parseXML($xmlFile);

			//create the fields
			self::createFields($xml);

			//create the virtuals
			self::createVirtuals($xml);

			//create the queries and the conditions
			self::createQueries($xml);

			//store the xml and mark the class as completed by doing so
			self::$xmlTree[$key] = $xml;
		}
	}

	/**
	* Stores the current object back to the database. This function only does an incremental update, meaning that only the changed fields are updated.
	* The update reflects the changes made to the object, and does not consider updates to the database in the time between retrieval of this object
	* and the calling of this function.
	* This update is executed without the use of transactions.
	* @return integer The amount of affected rows
	*/
	public final function storePrimary()
	{
		$fieldMap = self::validateMap(self::$fieldMap);

		$vec = new \System\Collection\Vector();
		foreach ($fieldMap as $fieldName=>$fieldData)
		{
			if ((isset($fieldData['primarykey'])) &&
				(mb_strtolower((string)$fieldData['primarykey']) == 'true'))
			{
				$dataMap = $this->validateInstanceMap($this->data);
				//we get the primary key entry from the values and use that as the condition value
				$vec[] = $dataMap[mb_strtolower((string)$fieldName)];
				break;
			}
		}

		if (!$vec->hasItems())
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException('There is no primarykey attribute set in the XML specification');
		}

		return $this->store(self::PRIMARYKEY_CONDITION_NAME_STORE, $vec);
	}

	/**
	* Stores the current object back to the database. This function only does an incremental update, meaning that only the changed fields are updated.
	* The update reflects the changes made to the object, and does not consider updates to the database in the time between retrieval of this object
	* and the calling of this function.
	* This update is executed without the use of transactions.
	* @param string The condition to use for the update
	* @param \System\Collection\Vector The parameters for the condition
	* @return integer The amount of affected rows
	*/
	public function store($condition, \System\Collection\Vector $parameters)
	{
		$fieldMap = self::validateMap(self::$fieldMap);
		$conditionMap = self::validateMap(self::$conditionMap);
		$modifications = $this->validateInstanceMap($this->modifications);
		$virtualModifications = $this->validateInstanceMap($this->virtualModifications);
		$dataMap = $this->validateInstanceMap($this->data);

		//we  dont do anything if we dont have anything to do, no modifications
		if ((!$modifications->hasItems()) &&
			(!$virtualModifications->hasItems()))
		{
			return 0;
		}

		if (!$conditionMap->keyExists($condition))
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException('Invalid condition given. Condition is not defined in ' . $this->getClassName() . '.');
		}

		//create the query
		$tuples = new \System\Collection\Vector();
		foreach ($modifications as $modification)
		{
			$dataField = $fieldMap->$modification;
			$tuples[] = "`" . mb_strtolower((string)$dataField['dbkey']) . "` = %?%";
		}

		//iterate over all the virtual modifications as we do want to reflect those
		foreach ($virtualModifications as $virtualModification)
		{
			$tuples[] = "`" . mb_strtolower($virtualModification) . "` = %?%";
		}

		//we get the table definitions again. could have saved this at load, but now we reduce memory footprint at the cost of neglectable slower saving.
		$querySources = self::createQuerySources(self::$xmlTree[get_class($this)]);
		$conditionString = $conditionMap->$condition;

		$sql = 'UPDATE ' . $querySources . ' SET ' . $tuples->convertToString() . ' ' . $conditionString;
		$query = new \System\Db\Query($this->getDatabase(), $sql);

		//bind the values to the query for setting the new values
		foreach ($modifications as $modification)
		{
			$fieldData = $fieldMap->$modification;
			$modification = mb_strtolower($modification);

			//get the type of the parameter. only integers and strings are supported
			switch (mb_strtolower((string)$fieldData['type']))
			{
				case \System\Type::TYPE_BOOL:
				case \System\Type::TYPE_BOOLEAN:
				case \System\Type::TYPE_INT:
				case \System\Type::TYPE_INTEGER:
					$type = \System\Db\QueryType::TYPE_INTEGER;
					break;
				default:
					$type = \System\Db\QueryType::TYPE_STRING;
			}

			//we first check if the new value is set to nullify, if so, we actually store NULL
			if ((isset($fieldData['nullify'])) &&
				((string)$fieldData['nullify'] == $dataMap->$modification))
			{
				$query->bind('NULL', \System\Db\QueryType::TYPE_QUERY);
			}
			//if this field has an encryption method set, we use that, otherwise just use the value itself
			elseif (isset($fieldData['encoding']))
			{
				switch (mb_strtolower((string)$fieldData['encoding']))
				{
					case self::ENCRYPTION_BASE64:
						$type = \System\Db\QueryType::TYPE_STRING;
						$query->bind(self::encodeBase64($dataMap->$modification), $type);
						break;
					case self::ENCRYPTION_XOR:
						$type = \System\Db\QueryType::TYPE_STRING;
						$query->bind(self::encodeXOR($dataMap->$modification), $type);
						break;
					case self::ENCRYPTION_AES:
						$type = \System\Db\QueryType::TYPE_STRING;
						$query->bind(self::encodeAES($dataMap->$modification), $type);
						break;
					default:
						throw new \System\Error\Exception\ObjectLoaderSourceException('The given encryptionmethod is not supported.');
				}
			}
			else
			{
				$query->bind($dataMap->$modification, $type);
			}
		}

		//bind the values to the query for setting the new values
		foreach ($virtualModifications as $virtualModification)
		{
			$type = \System\Db\QueryType::TYPE_STRING;
			$virtualField = 'virtual_' . $virtualModification;
			$value = $dataMap->$virtualField;

			$query->bind((string)$value, $type);
		}

		//bind the condition values to the query
		$val = new \System\Security\Validate();
		foreach ($parameters as $index=>$param)
		{
			//we need to decide the type of the parameter. Currently we only support integers and strings.
			$type = \System\Db\QueryType::TYPE_INTEGER;
			if ($val->isInt($param, $index, null, null, true) == \System\Security\ValidateResult::VALIDATE_INVALIDVALUE)
			{
				$type = \System\Db\QueryType::TYPE_STRING;
			}
			$query->bind($param, $type);
		}

		//execute the query
		$this->getDatabase()->query($query);

		//reset the modified list, because we already stored this. No need to store the same things at successive calls.
		$modifications->clear();

		return $this->getDatabase()->getAffectedRows();
	}

	/**
	* Encodes the given value to Base64 using the techniques and keys from the DynamicBaseObj
	* @param string The value to encode
	* @return string The encoded value
	*/
	public static final function encodeBase64($value)
	{
		return \System\Security\Base64Encoding::Base64Encode($value, self::ENCRYPTION_KEY);
	}

	/**
	* Decodes a given Base64 encoded string using the techniques and keys from the DynamicBaseObj.
	* @param string The encoded value
	* @return string The decoded value
	*/
	public static final function decodeBase64($value)
	{
		return \System\Security\Base64Encoding::Base64Decode($value, self::ENCRYPTION_KEY);
	}

	/**
	* Encodes the given value to XOR using the techniques and keys from the DynamicBaseObj
	* @param string The value to encode
	* @return string The encoded value
	*/
	public static final function encodeXOR($value)
	{
		return \System\Security\XOREncoding::XOREncrypt($value, self::ENCRYPTION_KEY);
	}

	/**
	* Decodes a given XOR encoded string using the techniques and keys from the DynamicBaseObj.
	* @param string The encoded value
	* @return string The decoded value
	*/
	public static final function decodeXOR($value)
	{
		return \System\Security\XOREncoding::XORDecrypt($value, self::ENCRYPTION_KEY);
	}

	/**
	* Encodes the given value to AES using the techniques and keys from the DynamicBaseObj
	* @param string The value to encode
	* @return string The encoded value
	*/
	public static final function encodeAES($value)
	{
		return \System\Security\AESEncoding::encode($value, self::ENCRYPTION_KEY, \System\Security\AESEncoding::CYPHER_128);
	}

	/**
	* Decodes the given value to an array. This function is primarily used for internal processes
	* and should not be called directly.
	* @param string The value to decode
	* @return array The decoded array
	*/
	public static final function decodeArray($value)
	{
		if (empty($value))
		{
			return array();
		}
		if ($b64 = base64_decode($value, true))
		{
			return unserialize($b64);
		}
		return unserialize(html_entity_decode($value, ENT_QUOTES));
	}

	/**
	* Encodes the given input and stores it using serialization and array techniques.
	* This function should not be called directly.
	* @param mixed The value to encode
	* @return string The encoded string.
	*/
	public static final function encodeArray($value)
	{
		return base64_encode(serialize($value));
	}

	/**
	* Decodes a given AES encoded string using the techniques and keys from the DynamicBaseObj.
	* @param string The encoded value
	* @return string The decoded value
	*/
	public static final function decodeAES($value)
	{
		return \System\Security\AESEncoding::decode($value, self::ENCRYPTION_KEY, \System\Security\AESEncoding::CYPHER_128);
	}

	/**
	* Creates the query selectors based on the given fields
	* @param \SimpleXMLElement The tree to work with
	* @param array The selectors as output, by reference
	*/
	private static final function createQuerySelectors(\SimpleXMLElement $xml, array &$selectors)
	{
		$fieldsMap = self::validateMap(self::$fieldMap);
		foreach ($fieldsMap as $fieldName=>$fieldData)
		{
			$source = "`" . mb_strtolower((string)$fieldData['dbkey']) . "`";
			$field = '`' . mb_strtolower((string)$fieldName) . '`';

			$item = $source . ' AS ' . $field;
			$selectors[] = $item; //added by reference

			//check if the field has the primary key set and then create it into the source xml
			if ((isset($fieldData['primarykey'])) &&
				(mb_strtolower((string)$fieldData['primarykey']) == 'true'))
			{
				//generate two conditions into the xml source. We use a different key for storing and loading as saving over multiple tables doesnt allow a LIMIT directive.
				if (!isset($xml->conditions))
				{
					//create the conditions branche if it doesnt exists.
					$xml->addChild('conditions');
				}
				$primaryConditionLoad = $xml->conditions->addChild('condition');
				$primaryConditionLoad->addAttribute('name', self::PRIMARYKEY_CONDITION_NAME_LOAD);
				$primaryConditionLoad->addChild('where', $source . ' = %?%');
				$primaryConditionLoad->addChild('limit', '1');
				$primaryConditionStore = $xml->conditions->addChild('condition');
				$primaryConditionStore->addAttribute('name', self::PRIMARYKEY_CONDITION_NAME_STORE);
				$primaryConditionStore->addChild('where', $source . ' = %?%');
				if ($xml->table->children()->count() == 0)
				{
					//only add a limit if there are no other sources
					$primaryConditionStore->addChild('limit', '1');
				}
			}
		}
	}

	/**
	* Builds the tables part based on the given XML tree
	* @param \SimpleXMLElement The tree to work with
	* @return string The table components of the SQL query
	*/
	private static final function createQuerySources(\SimpleXMLElement $xml)
	{
		$tables = '';

		//a table definition must be set, otherwise we don't know how to handle it
		if (!isset($xml->table))
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing table node.');
		}

		if (!isset($xml->table['name']))
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing name attribute in table definition.');
		}

		$tables .= '`' . $xml->table['name'] . '`';

		//we check the children of the table node to perform different types of joins, this is recursive.
		$level = $xml->table;
		while (count($level->children()) > 0)
		{
			foreach ($level->children() as $joinType=>$child)
			{
				if (!isset($child['name']))
				{
					throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing name attribute in source definition.');
				}

				switch (strtoupper($joinType))
				{
					case 'INNERJOIN':
						$tables .= ' INNER JOIN ';
						break;
					case 'LEFTJOIN':
						$tables .= ' LEFT JOIN ';
						break;
					case 'RIGHTJOIN':
						$tables .= ' RIGHT JOIN ';
						break;
					default:
						throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Invalid join element given.');
				}

				$tables .= '`' . $child['name'] . '` ON ';

				if (!isset($child['left']))
				{
					throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing left attribute in join definition.');
				}
				if (!isset($child['right']))
				{
					throw new \System\Error\Exception\ObjectLoaderSourceException('Malformed XML tree. Missing right attribute in join definition.');
				}

				$tables .= '`' . $child['left'] . '`';
				$tables .= ' = ';
				$tables .= '`' . $child['right'] . '`';
			}
			$level = $child;
		}

		return $tables;
	}

	/**
	* Returns the requested query for the current object.
	* The query will be retrieved from the prepared object and is ready to be used in the \System\Db\Query class.
	* Do note that it has not yet binded the retrieval values to it.
	* @param string The name of the condition to retrieve.
	* @return string The requested SQL query.
	*/
	public static final function queryForCondition($conditionName)
	{
		$conditions = self::validateMap(self::$queryMap);
		if (isset($conditions[$conditionName]))
		{
			/*
			we need to do a double decode because of the following:
			< goes through the sanitizor and gets cut off because it is an html start sign
			&lt; goes through the sanitizor and gets encoded to &amp;lt;
			to decode &amp;lt; we need to do a double decode.
			*/
			return html_entity_decode(html_entity_decode($conditions[$conditionName]));
		}
		else
		{
			if (($conditionName != self::PRIMARYKEY_CONDITION_NAME_LOAD) &&
				($conditionName != self::PRIMARYKEY_CONDITION_NAME_STORE))
			{
				throw new \System\Error\Exception\ObjectLoaderSourceException('The requested condition does not exist in this class definition: ' . $conditionName . ' in ' . get_called_class());
			}
			else
			{
				throw new \System\Error\Exception\ObjectLoaderSourceException('The primarykey attribute is not set in the XML class definition in ' . get_called_class());
			}
		}
	}

	/**
	* Loads the XML file from disk and parses it to an XML structure. This structure is then returned.
	* This function is used internally to parse the given DynamicBaseObj XML descriptors and should not
	* be called directly, as it serves no further purpose.
	* @param string The XML file to load
	* @return \SimpleXMLElement The XML tree
	*/
	public static final function parseXml($xmlFile)
	{
		try
		{
			if (!($xml = \simplexml_load_file($xmlFile)))
			{
				throw new \Exception('XML file does not exist, or could not be loaded: ' . $xmlFile);
			}
			else
			{
				/*
				check for xml inheritance. we currently support adding of parent fields, conditions and virtuals only
				we apply this recursively, to allow inheritance trees
				*/
				if (isset($xml['extends']))
				{
					$fileName = (string)$xml['extends'];
					$currentFilePath = new \System\IO\File($xmlFile);
					$parentFile = $currentFilePath->getPath() . $fileName;

					$parentXml = self::parseXml($parentFile);

					//we only add some field types in the parent xml to our current loaded xml
					foreach ($parentXml->children() as $item)
					{
						switch ($item->getName())
						{
							case 'fields':
								foreach ($item->children() as $field)
								{
									$result = $xml->xpath('fields/' . $field->getName());
									if ((is_array($result)) &&
										(count($result) == 0))
									{
										if (!isset($xml->fields))
										{
											$xml->addChild('fields');
										}
										\System\XML\XML::appendToXML($xml->fields, $field);
									}
								}
								break;
							case 'virtuals':
								foreach ($item->children() as $virtual)
								{
									$result = $xml->xpath('virtuals/' . $virtual->getName());
									if ((is_array($result)) &&
										(count($result) == 0))
									{
										if (!isset($xml->virtuals))
										{
											$xml->addChild('virtuals');
										}
										\System\XML\XML::appendToXML($xml->virtuals, $virtual);
									}
								}
								break;
							case 'conditions':
								foreach ($item->children() as $condition)
								{
									//conditions follow a different structure and work on the name attribute
									$result = $xml->xpath('conditions/condition[@name="' . $condition['name'] . '"]');
									if ((is_array($result)) &&
										(count($result) == 0))
									{
										if (!isset($xml->conditions))
										{
											$xml->addChild('conditions');
										}
										\System\XML\XML::appendToXML($xml->conditions, $condition);
									}
								}
								break;
							default:
								//we ignore other items
						}
					}
				}

				return $xml;
			}
		}
		catch (Exception $e)
		{
			throw new \System\Error\Exception\ObjectLoaderSourceException($e->getMessage());
		}
	}

	/**
	* Validates the given map and returns it.
	* It uses the called class as the key
	* @param mixed The map to be validated
	* @return \System\Collection\Map The map with the requested data for this instance
	*/
	private static final function validateMap(&$map)
	{
		//initialize the map for the entire system as it is reused by all the objects
		if ($map == null)
		{
			$map = new \System\Collection\Map();
		}

		//get the name of the current class and initialize the map for this object if it doesnt exists
		$key = get_called_class();
		if (!isset($map[$key]))
		{
			$map[$key] = new \System\Collection\Map();
		}

		return $map[$key];
	}

	/**
	* Validates the given map in a instance context
	* @param mixed The map to validate and instantiate
	* @return \System\Collection\Map The instanced map
	*/
	private final function validateInstanceMap(&$map)
	{
		if ($map == null)
		{
			$map = new \System\Collection\Map();
		}

		return $map;
	}

	/**
	* Dynamically loads objects from the relational database into xml based objects.
	* The object used must inherit from the \System\Base\DynamicBaseObj class.
	* @param \System\Db\Database The database object to query
	* @param mixed The parameters to be used as the primary key.
	* @param bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.
	* @param bool When true, the secondary db connection pipe is used to read the data from.
	* @return mixed If there is one result, then only an instance of the requested object is returned; when there are multiple results a \System\Db\DatabaseResult vector is returned, also see the $alwaysUseContainer parameter. If there are no results, null is returned
	* @see \System\Db\Object::loadPrimary();
	*/
	public static final function loadPrimary(\System\Db\Database $db, $primarykeyValue, $alwaysUseContainer = false, $useSecondaryDatabasePipe = true)
	{
		$classname = '\\' . get_called_class();
		return \System\Db\Object::loadPrimary($db, $classname, $primarykeyValue, $alwaysUseContainer, $useSecondaryDatabasePipe);
	}

	/**
	* Dynamically loads objects from the relational database into xml based objects.
	* The object used must inherit from the \System\Base\DynamicBaseObj class.
	* @param \System\Db\Database The database object to query
	* @param string The condition string as described in the objects xml file
	* @param \System\Collection\Vector The parameters to be used in the condition. If an item in the vector is a vector itself, its items are imploded by ', ' and treated as a \System\Db\QueryType::TYPE_QUERY parameter
	* @param bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.
	* @param bool When true, the secondary db connection pipe is used to read the data from.
	* @return mixed If there is one result, then only an instance of the requested object is returned; when there are multiple results a \System\Db\DatabaseResult vector is returned, also see the $alwaysUseContainer parameter. If there are no results, null is returned
	* @see \System\Db\Object::load();
	*/
	public static function load(\System\Db\Database $db, $condition, \System\Collection\Vector $parameters = null, $alwaysUseContainer = false, $useSecondaryDatabasePipe = true)
	{
		$classname = '\\' . get_called_class();
		return \System\Db\Object::load($db, $classname, $condition, $parameters, $alwaysUseContainer, $useSecondaryDatabasePipe);
	}

	/**
	* This function sets the fields recieved from the database and its xml structure.
	* This function should not be called directly and should only be used internally.
	* @param string The name of the property
	* @param mixed The value of the property
	*/
	public function __set($name, $value)
	{
		$dataMap = $this->validateInstanceMap($this->data);
		$fieldMap = self::validateMap(self::$fieldMap);

		if ($fieldMap->keyExists($name))
		{
			$fieldData = $fieldMap->$name;
			if (isset($fieldData['encoding']))
			{
				switch (mb_strtolower((string)$fieldData['encoding']))
				{
					case self::ENCRYPTION_BASE64:
						$dataMap[$name] = self::decodeBase64($value);
						break;
					case self::ENCRYPTION_XOR:
						$dataMap[$name] = self::decodeXOR($value);
						break;
					case self::ENCRYPTION_AES:
						$dataMap[$name] = self::decodeAES($value);
						break;
					default:
						throw new \System\Error\Exception\ObjectLoaderSourceException('The given encryptionmethod is not supported.');
				}

				return;
			}
		}

		//we modify null values to an empty string so there won't be any iteration errors
		if (is_null($value))
		{
			$value = '';
		}

		$dataMap[$name] = $value;
	}
}