<?php
/**
* APIGen.class.php
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


namespace System\Inspection;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* This class will generate and update the API documentation of the System namespace and the Module namespace
* @package \System\Inspection
*/
class APIGen extends \System\Base\StaticBase
{
	/**
	* The name of the constants file
	*/
	const CONSTANTS_FILENAME = 'constants.doc.php';

	/**
	* @var array Files to exclude
	*/
	private static $excludeFiles = array(
			'index.php');

	/**
	* @var array Extensions to copy from the sources
	*/
	private static $copyExtensions = array(
			'xml',
			'xsl',
			'css',
			'js',
			'less',
			'swf',
			'html',
			'htaccess');

	/**
	* Generates the API documentation for the System and the Module namespace.
	* It only includes PHP classes and will output PHP files in the given target directory.
	* This function is also applicable on a running system.
	* The target folder will be cleared!
	* @param \System\IO\Directory The target folder to use. This folder must be writable
	*/
	public static final function generateAPIDoc(\System\IO\Directory $targetFolder)
	{
		//delete all files before actual adding, so we dont append to old files
		$files = \System\IO\Directory::walkDir($targetFolder->getCurrentPath(), new \System\Collection\Vector('php'));
		foreach ($files as $file)
		{
			$file->delete();
		}

		//first we have to load all the php files in the current System and Module folders
		$systemFiles = \System\IO\Directory::walkDir(PATH_SYSTEM, new \System\Collection\Vector('php'));
		foreach ($systemFiles as $systemFile)
		{
			if (!in_array($systemFile->getFilename(), self::$excludeFiles))
			{
				require_once ($systemFile->getFullPath());
			}
		}

		$vec = \System\Module\Module::getAllModules();
		//echo '<pre>';
		$moduleList = new \System\Collection\Vector();
		foreach ($vec as $module)
		{
			$manifest = strtolower(str_ireplace('Module\\', '', str_ireplace('\Module', '', $module->manifest))) . '\\';
			$moduleList[] = $manifest;
		}

		$moduleFiles = \System\IO\Directory::walkDir(PATH_MODULES, new \System\Collection\Vector('php'));
		foreach ($moduleFiles as $moduleFile)
		{
			$base = $moduleFile->stripBase(PATH_MODULES);

			$found = false;
			foreach ($moduleList as $entry)
			{
				if (stripos(str_replace('\\', '/', $base), str_replace('\\', '/', $entry)) === 0)
				{
					$found = true;
					break;
				}
			}

			if ($found)
			{
				require_once ($moduleFile->getFullPath());
			}
		}

		foreach (get_declared_interfaces() as $interface)
		{
			$reflectionClass = new \ReflectionClass($interface);

			if (($reflectionClass->getFileName() != '') &&
				((strpos(\System\IO\Directory::normalize($reflectionClass->getFileName()), \System\IO\Directory::normalize(PATH_MODULES)) !== false) ||
				 (strpos(\System\IO\Directory::normalize($reflectionClass->getFileName()), \System\IO\Directory::normalize(PATH_SYSTEM)) !== false)))
			{
				self::generateClassDocumentation($targetFolder, $reflectionClass);
			}
		}

		foreach (get_declared_classes() as $class)
		{
			$reflectionClass = new \ReflectionClass($class);

			if (($reflectionClass->getFileName() != '') &&
				((strpos(\System\IO\Directory::normalize($reflectionClass->getFileName()), \System\IO\Directory::normalize(PATH_MODULES)) !== false) ||
				 (strpos(\System\IO\Directory::normalize($reflectionClass->getFileName()), \System\IO\Directory::normalize(PATH_SYSTEM)) !== false)))
			{
				self::generateClassDocumentation($targetFolder, $reflectionClass);
			}
		}

		/*$constants = get_defined_constants(true);
		echo '<pre>';
		$moduleConstants = new \System\Collection\Map();
		$systemConstants = new \System\Collection\Map();
		foreach ($constants['user'] as $key=>$value)
		{
			switch (true)
			{
				case stripos($key, 'system\\') === 0:
					$systemConstants[$key] = $value;
					break;
				case stripos($key, 'module\\') === 0:
					$moduleConstants[$key] = $value;
					break;
				default:
					//dont do anything
			}
		}

		foreach ($systemConstants as $key=>$value)
		{
			self::writeConstant($targetFolder, $key, $value);
		}

		foreach ($moduleConstants as $key=>$value)
		{
			self::writeConstant($targetFolder, $key, $value);
		}*/

		//copy the copy extensions
		$vec = new \System\Collection\Vector(self::$copyExtensions);
		self::copyFiles($targetFolder, PATH_MODULES, $vec, 'module');
		self::copyFiles($targetFolder, PATH_SYSTEM, $vec, 'system');
	}

	private static final function writeConstant(\System\IO\Directory $targetFolder, $key, $value)
	{
		$folderParts = preg_split('/\\\\/', $key);
		$name = array_pop($folderParts);

		$newPath = $targetFolder->getCurrentPath();
		foreach ($folderParts as $part)
		{
			$newPath = \System\IO\Directory::getPath($newPath . \System\IO\Directory::getSeparator() . $part);
			if (!file_exists($newPath))
			{
				mkdir($newPath, 0777, true);
			}
		}

		$fileContent = '';
		if (!file_exists($newPath . \System\IO\Directory::getSeparator() . self::CONSTANTS_FILENAME))
		{
			$fileContent = "<?php\r\n\r\n//AUTOGENERATED CONSTANTS FILE\r\n\r\nnamespace " . implode('\\', $folderParts) . ";\r\n\r\n";
		}
		else
		{
			$fileContent = file_get_contents($newPath . \System\IO\Directory::getSeparator() . self::CONSTANTS_FILENAME);
		}

		$fileContent .= "\r\nconst " . $name . " = '" . $value . "';\r\n";

		file_put_contents($newPath . \System\IO\Directory::getSeparator() . self::CONSTANTS_FILENAME, $fileContent);
	}

	private static final function copyFiles(\System\IO\Directory $targetFolder, $sourceFolder, \System\Collection\Vector $fileMask, $baseSubfolder)
	{
		$files = \System\IO\Directory::walkDir($sourceFolder, $fileMask);

		$target = new \System\IO\Directory($targetFolder->getCurrentPath() . \System\IO\Directory::getSeparator() . $baseSubfolder);

		foreach ($files as $file)
		{
			$baseFile = $file->stripBase($sourceFolder);

			$folderParts = preg_split('/\\\\/', $baseFile);
			array_pop($folderParts);

			$newPath = $target->getCurrentPath();
			foreach ($folderParts as $part)
			{
				$newPath = \System\IO\Directory::getPath($newPath . \System\IO\Directory::getSeparator() . $part);
				if (!file_exists($newPath))
				{
					mkdir($newPath, 0777, true);
				}
			}
			$newPath .= \System\IO\Directory::getSeparator() . $file->getFilename();
			$file->copyFile($newPath);
		}
	}

	/**
	* Generates the class documentation. It scans the current given class and creates its own
	* PHP code block for it.
	* @param \System\IO\Directory The folder to write the class def to.
	* @param \ReflectionClass The instantiated reflection class to work with.
	*/
	private static final function generateClassDocumentation(\System\IO\Directory $targetFolder, \ReflectionClass $class)
	{
		$code = '<?php' . "\r\n\r\n";

		$code .= self::getCopyrightNotice();

		if ($class->getNamespaceName())
		{
			$code .= 'namespace ' . $class->getNamespaceName() . ';' . "\r\n\r\n";
		}

		if (($class->isAbstract()) &&
			(!$class->isInterface()))
		{
			$code .= 'abstract ';
		}

		if ($class->isFinal())
		{
			$code .= 'final ';
		}

		if ($class->isInterface())
		{
			$code .= 'interface ';
		}
		else
		{
			$code .= 'class ';
		}

		$code .= $class->getShortName();

		if ($class->getParentClass())
		{
			$code .= ' extends ' . $class->getParentClass()->getName();
		}

		$interfaces = $class->getInterfaces();
		if (count($interfaces) > 0)
		{
			$code .= ($class->isInterface() ? ' extends ' : ' implements ');
			$code .= implode(', ', $class->getInterfaceNames());
		}

		$code .= "\r\n{\r\n";

		$constants = $class->getConstants();
		self::addClassConstants($code, $constants);

		$properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_STATIC);
		self::addClassProperties($code, $properties, $class);

		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_ABSTRACT | \ReflectionMethod::IS_FINAL | \ReflectionMethod::IS_STATIC);
		self::addClassMethods($code, $methods, $class);

		//if the class is a dynamic base object, we want to add the dynamic methods to the class' documentation
		if ($class->isSubclassOf('\System\Base\DynamicBaseObj'))
		{
			self::addClassDynamicBaseMethods($code, $class);
		}

		$code .= "}\r\n";

		$code .= "?>\r\n";

		self::storeToFile($targetFolder, $class, $code);
	}

	/**
	* Generates the dynamic base documentation. It scans the current given class and creates its own
	* PHP code block for it.
	* @param string The string to append to. By reference.
	* @param \ReflectionClass The instantiated reflection class to work with.
	*/
	private static final function addClassDynamicBaseMethods(&$code, \ReflectionClass $class)
	{
		$reflectionMethod = $class->getMethod('getXMLSourceFile');
		if (($reflectionMethod) &&
			(!$reflectionMethod->isAbstract()))
		{
			$xmlFile = $reflectionMethod->invoke(null);
			$xmlTree = \System\Base\DynamicBaseObj::parseXml($xmlFile);

			//parse the regular methods
			self::parseDynamicBaseFieldMethods($code, $class, $xmlTree);

			//parse the virtual methods
			self::parseDynamicBaseVirtualMethods($code, $class, $xmlTree);

			//parse the condition loading methods
			self::parseDynamicBaseConditionMethods($code, $class, $xmlTree);
		}
	}

	/**
	* Generates the dynamic base documentation for condtion blocks. It scans the current given class and creates its own
	* PHP code block for it.
	* @param string The string to append to. By reference.
	* @param \ReflectionClass The instantiated reflection class to work with.
	* @param \SimpleXMLElement The parse tree for the dynamic base object
	*/
	private static final function parseDynamicBaseConditionMethods(&$code, \ReflectionClass $class, \SimpleXMLElement $xmlTree)
	{
		if (isset($xmlTree->conditions))
		{
			foreach ($xmlTree->conditions->children() as $method)
			{
				if (isset($method['name']))
				{
					$name = (string)$method['name'];

					$name = str_ireplace(' ', '', ucwords(str_ireplace('_', ' ', $name)));

					$loadName = 'load' . $name;
					if (!$class->hasMethod($loadName))
					{
						$code .= '/**' . "\r\n";
						$code .= '* Loads the object statically from the condition: ' . (string)$method['name'] . '. [DYNAMICBASE]' . "\r\n";
						$code .= '* The function name is translated to a condition. Uppercase gets transformed to lowercase and prepended by an underscore.' . "\r\n";
						$code .= '* Example loadImageId() translates to the condition image_id.' . "\r\n";
						$code .= '* @param \System\Db\Database The database to load the object(s) from.' . "\r\n";

						$paramCount = 0;
						if (isset($method->where))
						{
							$paramCount += substr_count((string)$method->where, '%?%');
						}
						if (isset($method->groupby))
						{
							$paramCount += substr_count((string)$method->groupby, '%?%');
						}
						if (isset($method->orderby))
						{
							$paramCount += substr_count((string)$method->orderby, '%?%');
						}
						if (isset($method->limit))
						{
							$paramCount += substr_count((string)$method->limit, '%?%');
						}

						$params = array();
						for ($i = 0; $i < $paramCount; $i++)
						{
							$code .= '* @param mixed A parameter for this load condition' . "\r\n";
							$params[] = '$parameter' . ($i + 1);
						}
						//we add an empty element to make sure we get a separator
						if (count($params) > 0)
						{
							$params[] = '';
						}
						$code .= '* @param bool When true, the result will always be wrapped in a \System\Db\DatabaseResult vector, even if there is only one result.' . "\r\n";
						$code .= '* @param bool When true, the secondary db connection pipe is used to read the data from.' . "\r\n";
						$code .= '* @param \System\Db\Database The database to load the object(s) from.' . "\r\n";
						$code .= '*/' . "\r\n";
						$code .= 'public static function ' . $loadName . '(\System\Db\Database $db, ' . implode(', ', $params) . '$alwaysUseContainer = false, $useSecondaryDatabasePipe = true) {}';
						$code .= "\r\n\r\n";
					}
				}
			}
		}
	}

	/**
	* Generates the dynamic base documentation for field blocks. It scans the current given class and creates its own
	* PHP code block for it.
	* @param string The string to append to. By reference.
	* @param \ReflectionClass The instantiated reflection class to work with.
	* @param \SimpleXMLElement The parse tree for the dynamic base object
	*/
	private static final function parseDynamicBaseFieldMethods(&$code, \ReflectionClass $class, \SimpleXMLElement $xmlTree)
	{
		if (isset($xmlTree->fields))
		{
			foreach ($xmlTree->fields->children() as $method)
			{
				if ((!isset($method['noset'])) ||
					((isset($method['noset'])) && (strtolower($method['noset']) != 'true')))
				{
					//if the class has the method already defined, we skip the field function definition
					if (!$class->hasMethod('set' . ucfirst($method->getName())))
					{
						$code .= '/**' . "\r\n";
						$code .= '* Sets the ' . $method->getName() . ' property. [DYNAMICBASE]' . "\r\n";
						$code .= '* Use the store() or storePrimary() function to make this change final.' . "\r\n";
						switch (strtolower($method['type']))
						{
							case 'timestamp':
								$returnType = '\System\Calendar\Time';
								break;
							case 'boolean':
							case 'bool':
								$returnType = 'bool';
								break;
							case 'int':
							case 'integer':
								$returnType = 'int';
								break;
							case 'string':
								$returnType = 'string';
								break;
							default:
								$returnType = 'mixed';
						}
						$code .= '* @param ' . $returnType . ' The parameter' . "\r\n";
						$code .= '* @return \\' . $class->getName() . ' The current instance of the object' . "\r\n";
						$code .= '*/' . "\r\n";
						$code .= 'public function set' . ucfirst($method->getName()) . '($parameters) {}';
						$code .= "\r\n\r\n";
					}
				}

				if ((!isset($method['noget'])) ||
					((isset($method['noget'])) && (strtolower($method['noget']) != 'true')))
				{
					$code .= '/**' . "\r\n";
					$code .= '* Gets the ' . $method->getName() . ' property. [DYNAMICBASE]' . "\r\n";

                                        $append = '';
					switch (strtolower($method['type']))
					{
						case 'timestamp':
							$returnType = '\System\Calendar\Time';
							break;
						case 'boolean':
						case 'bool':
							$returnType = 'bool';

							//if the class has the method already defined, we skip the field function definition
							if (!$class->hasMethod('is' . ucfirst($method->getName())))
							{
								$append = '/**' . "\r\n";
								$append .= '* Gets the boolean result of the ' . $method->getName() . ' property. [DYNAMICBASE]' . "\r\n";
								$append .= '* @return bool Boolean evaluation of the property value' . "\r\n";
								$append .= '*/' . "\r\n";
								$append .= 'public function is' . ucfirst($method->getName()) . '() {}';
								$append .= "\r\n\r\n";
							}
							break;
						case 'int':
						case 'integer':
							$returnType = 'int';
							break;
						case 'string':
							$returnType = 'string';
							break;
						default:
							$returnType = 'mixed';
					}

					//if the class has the method already defined, we skip the field function definition
					if (!$class->hasMethod('get' . ucfirst($method->getName())))
					{
						$code .= '* @return ' . $returnType . ' The property value' . "\r\n";
						$code .= '*/' . "\r\n";
						$code .= 'public function get' . ucfirst($method->getName()) . '() {}';
						$code .= "\r\n\r\n";
					}

                                        //if the class has the method already defined, we skip the field function definition
					if (!$class->hasMethod('is' . ucfirst($method->getName())))
					{
						$code .= $append;
					}
				}
			}
		}
	}

	/**
	* Generates the dynamic base documentation for virtual blocks. It scans the current given class and creates its own
	* PHP code block for it.
	* @param string The string to append to. By reference.
	* @param \ReflectionClass The instantiated reflection class to work with.
	* @param \SimpleXMLElement The parse tree for the dynamic base object
	*/
	private static final function parseDynamicBaseVirtualMethods(&$code, \ReflectionClass $class, \SimpleXMLElement $xmlTree)
	{
		if (isset($xmlTree->virtuals))
		{
			foreach ($xmlTree->virtuals->children() as $method)
			{
				//if the class has the method already defined, we skip the virtual function definition
				if (!$class->hasMethod('get' . ucfirst($method->getName())))
				{
					$code .= '/**' . "\r\n";
					$code .= '* Gets the virtual ' . $method->getName() . ' property. This is a virtual method and retrieves a dynamic base object, or if many a Vector of objects.' . "\r\n";
					$code .= '* The database parameter is optional. When no database is given, the same database as the current object is used. When a database is given, that database is used instead. [DYNAMICBASE] [VIRTUALMETHOD]' . "\r\n";
					$code .= '* @param \System\Db\Database The database to query (optional)' . "\r\n";
					$code .= '* @return ' . (((mb_strtolower((string)$method['alwaysusecontainer']) == 'true') || (mb_strtolower((string)$method['alwaysusecontainer']) == 'yes')) ? '\System\Db\DatabaseResult A vector with ' . $method['type'] . ' objects' : $method['type'] . ' The property value, or null if there are no results') . "\r\n";
					$code .= '*/' . "\r\n";
					$code .= 'public function get' . ucfirst($method->getName()) . '(\System\Db\Database $db = null) {}';
					$code .= "\r\n\r\n";
				}

				//if the class has the method already defined, we skip the virtual function definition
				if ((isset($method['setmethod'])) &&
					(!$class->hasMethod('set' . ucfirst($method->getName()))))
				{
					$code .= '/**' . "\r\n";
					$code .= '* Sets the virtual ' . $method->getName() . ' property. This is a virtual method and sets the corresponding value field, and its corresponding datafields.' . "\r\n";
					$code .= '* Fields that use the same underlying data field will also be updated and set as modified.' . "\r\n";
					$code .= '* The value is stored in the current instance, and will be finialized when de store functions are used. [DYNAMICBASE] [VIRTUALMETHOD]' . "\r\n";
					$code .= '* @param ' . $method['type'] . ' The database to query (optional)' . "\r\n";
					$code .= '* @return ' . $class->getName() . ' The current instance' . "\r\n";
					$code .= '*/' . "\r\n";
					$code .= 'public function set' . ucfirst($method->getName()) . '(' . $method['type'] . ' $parameter) {}';
					$code .= "\r\n\r\n";
				}
			}
		}
	}

	/**
	* Adds a single class method to the given code. The methodname parameter will be used instead of the
	* name in the $method variable. This allows for virtual methods to be outputted.
	* @param string The string to append to. By reference.
	* @param \ReflectionMethod The method to reflect upon
	* @param string The name to use as the methodName
	* @param \ReflectionClass The class that contains this method
	* @param string Overrides the original method documentation. On empty, it uses the default original.
	*/
	private static final function addClassMethod(&$code, \ReflectionMethod $method, $methodName, \ReflectionClass $class, $methodDocumentation = '')
	{
		if (($class->isSubclassOf('\System\Base\DynamicBaseObj')) &&
			(($method->getName() == 'load') || $method->getName() == 'loadPrimary'))
		{
			$code .= (empty($methodDocumentation) ? str_ireplace('@return mixed', '@return \\' . $class->getName(), $method->getDocComment()) . "\r\n" : $methodDocumentation . "\r\n");
		}
		else
		{
			$code .= (empty($methodDocumentation) ? $method->getDocComment() . "\r\n" : $methodDocumentation . "\r\n");
		}

		if ($method->isPublic())
		{
			$code .= 'public ';
		}
		if ($method->isProtected())
		{
			$code .= 'protected ';
		}
		if (($method->isAbstract()) &&
			(!$class->isInterface()) && //interface methods are always abstract
			(!$method->isStatic())) //prevent static abstract definitions from instances
		{
			$code .= 'abstract ';
		}
		if ($method->isStatic())
		{
			$code .= 'static ';
		}
		if ($method->isFinal())
		{
			$code .= 'final ';
		}

		$code .= 'function ' . $methodName . "(";
		$parameters = $method->getParameters();
		self::addMethodParameters($code, $parameters);
		$code .= ')';

		if ($method->isAbstract())
		{
			$code .= ';';
		}
		else
		{
			$code .= ' {}';
		}

		$code .= "\r\n\r\n";
	}

	/**
	* Adds class methods to the given code string.
	* @param string The string to append to. By reference.
	* @param array An array with \ReflectionMethod instances
	* @param \ReflectionClass The class that contains this method
	*/
	private static final function addClassMethods(&$code, array $methods, \ReflectionClass $class)
	{
		foreach ($methods as $method)
		{
			/** @var \ReflectionMethod */
			$method = $method;

			//we dont document internal api's
			if ($method->isPrivate())
			{
				continue;
			}

			//we skip the magic functions, but do include constructors and magic contructors
			if ((substr($method->getName(), 0, 2) == '__') &&
				(mb_strpos($method->getName(), '__construct') === false) &&
				(mb_strpos($method->getName(), '__destruct') === false))
			{
				continue;
			}

			//For classes based on SingletonBase, we create a more descriptive getInstance function.
			if (($method->getDeclaringClass()->getName() == 'System\Base\SingletonBase') &&
				($method->getName() == 'getInstance'))
			{
				$documentation = "/**\n\r* Retrieves the single instance of the current class.\r\n* @return \\" . $class->getName() . " The only existing instance of the current class.\r\n*/";
				self::addClassMethod($code, $method, 'h_' . $method->getName(), $class, $documentation);
				self::addClassMethod($code, $method, 't_' . $method->getName(), $class, $documentation);
				self::addClassMethod($code, $method, 'm_' . $method->getName(), $class, $documentation);
				self::addClassMethod($code, $method, 'c_' . $method->getName(), $class, $documentation);
				self::addClassMethod($code, $method, $method->getName(), $class, $documentation);
				continue;
			}

			//this also adds virtual methods to the documentation, for easy autocompletion in the IDE, but we skip self made magic functions (own constructors)
			if (($method->getDeclaringClass()->isSubclassOf('\System\Base\BaseObj')) &&
				(!$method->isConstructor()) &&
				(!$method->isDestructor()) &&
				(mb_strpos($method->getName(), '__') === false))
			{
				self::addClassMethod($code, $method, 'h_' . $method->getName(), $class);
				self::addClassMethod($code, $method, 't_' . $method->getName(), $class);
				self::addClassMethod($code, $method, 'm_' . $method->getName(), $class);
				self::addClassMethod($code, $method, 'c_' . $method->getName(), $class);
			}

			self::addClassMethod($code, $method, $method->getName(), $class);
		}
	}

	/**
	* Adds parameters for a method the the given code block
	* @param string The string to append to. By reference.
	* @param array An array filled with \ReflectionParameter instances
	*/
	private static final function addMethodParameters(&$code, array $parameters)
	{
		$params = array();
		foreach ($parameters as $parameter)
		{
			$str = '';
			if ($parameter->isArray())
			{
				$str .= 'array ';
			}

			if ($parameter->getClass())
			{
				$str .= '\\' . $parameter->getClass()->getName() . ' ';
			}

			if ($parameter->isPassedByReference())
			{
				$str .= '&';
			}

			$str .= '$' . $parameter->getName();

			if (($parameter->isOptional()) &&
				($parameter->isDefaultValueAvailable()))
			{
				$str .= ' = ';
				switch (\System\Type::getType($parameter->getDefaultValue()))
				{
					case \System\Type::TYPE_ARRAY:
						$str .= 'array()';
						break;
					case \System\Type::TYPE_STRING:
						$str .= '\'' . $parameter->getDefaultValue() . '\'';
						break;
					case \System\Type::TYPE_INTEGER:
					case \System\Type::TYPE_DOUBLE:
					case \System\Type::TYPE_NULL:
					case \System\Type::TYPE_BOOLEAN:
						$str .= \System\Type::getValue($parameter->getDefaultValue());
						break;
					default:
						//ignore
						break;
				}
			}

			$params[] = $str;
		}

		$code .= implode(', ', $params);
	}

	/**
	* Adds class properties to the given code block.
	* @param string The string to append to. By reference.
	* @param array An array with \ReflectionProperty instances
	* @param \ReflectionClass The class that contains the properties
	*/
	private static final function addClassProperties(&$code, array $properties, \ReflectionClass $class)
	{
		foreach ($properties as $property)
		{
			//we don't want to show our privates
			if ($property->isPrivate())
			{
				continue;
			}
			//if the property is generated on runtime, we dont want to show it
			if (!$property->isDefault())
			{
				continue;
			}

			$code .= $property->getDocComment() . "\r\n";
			if ($property->isPublic())
			{
				$code .= 'public ';
			}
			if ($property->isProtected())
			{
				$code .= 'protected ';
			}
			if ($property->isStatic())
			{
				$code .= 'static ';
			}
			$code .= '$' . $property->getName() . ";\r\n\r\n";

			//support for auto generated getters and setters
			if (mb_stripos($property->getDocComment(), '@publicget') !== false)
			{
				$code .= str_ireplace('@var ', '@return ', $property->getDocComment()) . "\r\n";
				$code .= 'public function get' . ucfirst($property->getName()) . "() {} \r\n\r\n";

				if (mb_stripos($property->getDocComment(), '@var bool') !== false)
				{
					$code .= str_ireplace('@var ', '@return ', $property->getDocComment()) . "\r\n";
					$code .= 'public function is' . ucfirst($property->getName()) . "() {} \r\n\r\n";
				}
			}
			if (mb_stripos($property->getDocComment(), '@publicset') !== false)
			{
				$code .= str_ireplace('@var ', '@param ', $property->getDocComment()) . "\r\n";
				$code .= 'public function set' . ucfirst($property->getName()) . '($value) {} ' . "\r\n\r\n";
			}
		}
	}

	/**
	* Adds class constants to the given code block.
	* @param string The string to append to. By reference.
	* @param array Constants in an array, the key is their name; the value its value.
	*/
	private static final function addClassConstants(&$code, array $constants)
	{
		foreach ($constants as $constantName=>$constantValue)
		{
			$str = '';
			switch (\System\Type::getType($constantValue))
			{
				case \System\Type::TYPE_ARRAY:
					$str = 'array()';
					break;
				case \System\Type::TYPE_STRING:
					$str = '\'' . $constantValue . '\'';
					break;
				case \System\Type::TYPE_INTEGER:
				case \System\Type::TYPE_DOUBLE:
				case \System\Type::TYPE_NULL:
				case \System\Type::TYPE_BOOLEAN:
					$str = \System\Type::getValue($constantValue);
					break;
				default:
					$str = '\'undefined\'';
					break;
			}
			$code .= 'const ' . $constantName . ' = ' . $str . ';' . "\r\n";
		}
	}

	/**
	* Stores the given codeblock to its appropriate file. It appends the block to the end of the file.
	* @param \System\IO\Directory The target base folder
	* @param \ReflectionClass The class whoms definition to store
	* @param string The code to place in the sourcefile.
	*/
	private static final function storeToFile(\System\IO\Directory $targetFolder, \ReflectionClass $class, $code)
	{
		//store the contents to a file
		$targetFolderStr = \System\IO\Directory::getPath($targetFolder->getCurrentPath(true) . strtolower($class->getNamespaceName()) . \System\IO\Directory::getSeparator());
		if (!file_exists($targetFolderStr))
		{
			mkdir($targetFolderStr, 0777, true);
		}
		$targetFileName = $targetFolderStr . basename($class->getFileName());
		file_put_contents($targetFileName, $code, LOCK_EX | FILE_APPEND);
	}

	/**
	* Writes a copyright notice to the code block.
	*
	*/
	private static final function getCopyrightNotice()
	{
		$str = '
			/**
			* Notice: This file is copyrighted and unauthorized use is strictly prohibited.
			* The file contains proprietary information and may not be distributed, modified
			* or altered in any way, without written perimission.
			* Copyright: SUPERHOLDER B.V.
			*/' . "\r\n";

		return $str;
	}
}
