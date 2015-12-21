<?php
/**
* FileDirector.class.php
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


namespace System\IO;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the FileDirector
* @package \System\IO
*/
class FileDirector extends \System\Base\StaticBase
{
	/**
	* The length of the container to use. This equals the nesting depth.
	*/
	const CONTAINER_NAME_USAGE_LENGTH = 5;

	/**
	* Gets the name of the container in a normalized form
	* @param string The full name of the container
	* @return string The normalized container name
	*/
	private static function getContainerNameParts($container)
	{
		$containerNameUsage = mb_strtolower(mb_substr($container, 0, self::CONTAINER_NAME_USAGE_LENGTH));

		if (mb_strlen($containerNameUsage) < self::CONTAINER_NAME_USAGE_LENGTH)
		{
			$containerNameUsage = str_pad($containerNameUsage, self::CONTAINER_NAME_USAGE_LENGTH, '0', STR_PAD_BOTH);
		}

		return $containerNameUsage;
	}

	/**
	* Retrieves the container folder and optionally creates a new folder if required.
	* @param Directory The base path to use
	* @param string The name of the container. Uses the first CONTAINER_NAME_USAGE_LENGTH characters of the string. If the string is smaller, Its get zero-padded.
	* @param bool True to create the folder if it does not exists, false otherwise.
	* @return Directory Returns the requested path with the container components
	*/
	protected static function getContainerFolder(\System\IO\Directory $baseFolder, $container, $createIfNotExists)
	{
		$containerNameUsage = self::getContainerNameParts($container);

		$containerNameParts = str_split($containerNameUsage);
		$containerPath = implode(\System\IO\Directory::getSeparator(), $containerNameParts);

		$basePath = new \System\IO\Directory($baseFolder->getCurrentPath() . $containerPath, false);
		if ((!$basePath->exists()) &&
			($createIfNotExists))
		{
			$oldMask = umask(0);
			mkdir($basePath->getCurrentPath(), \System\IO\Directory::FULL_ACCESS, true);
			umask($oldMask);
		}

		if (!$basePath->exists())
		{
			throw new \System\Error\Exception\FileNotFoundException('Could not create the given folder: ' . $basePath->getCurrentPath());
		}

		return $basePath;
	}

	/**
	* Gets the file from the local filesystem.
	* @param string The filename, this is just the basename including extension
	* @param Directory The base folder for storing.
	* @param string The name of the container
	* @return File The returned file
	*/
	public static function getFile($filename, \System\IO\Directory $baseFolder, $container)
	{
		$folder = self::getContainerFolder($baseFolder, $container, false);
		$file = new \System\IO\File($folder->getCurrentPath(true) . $filename);
		return $file;
	}

	/**
	* Gets the container path to write to. If the directory is not present, it will be created
	* @param Directory The base folder for storing.
	* @param string The name of the container
	* @return Directory The directory to write to
	*/
	public static function getWritablePath(\System\IO\Directory $baseFolder, $container)
	{
		return self::getContainerFolder($baseFolder, $container, true);
	}

	/**
	* Gets the url name of the file. This will be in the following form:
	* if $file = 'abc.jpg', and container = 'myRandomContainer', then
	* retrun value is 'abc.myran.jpg'
	* @param File The file to use
	* @param string The container to use
	* @return string The name of the url file. This excludes base paths.
	*/
	public static function getURLFile(\System\IO\File $file, $container)
	{
		$filename = $file->getFilename();
		$extension = $file->getExtension();
		$filename = mb_substr($filename, 0, -mb_strlen($extension));

		$containerNameUsage = self::getContainerNameParts($container);
		$filename .= $containerNameUsage . '.' . $file->getExtension();

		return $filename;
	}
}