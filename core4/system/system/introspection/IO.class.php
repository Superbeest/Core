<?php
/**
* IO.class.php
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


namespace System\System\Introspection;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* Implements the functionality to fingerprint a folder
* @package \System\System\Introspection
*/
final class IO extends \System\Base\StaticBase
{
	/**
	* Returns the hashes for the given files in the given folder.
	* This function gives the hash per file in the folder.
	* @param \System\IO\Directory the directory to get the hashes from
	* @return \System\Collection\Map A map with hashes per file
	*/
	public static final function getFileFingerprint(\System\IO\Directory $directory)
	{
		$fileHashes = new \System\Collection\Map();

		//new \System\Collection\Vector('php')
		$files = $directory->getFiles();

		foreach ($files as $file)
		{
			/** @var \System\IO\File */
			$file = $file;

			$descriptors = new \System\Collection\Map();
			$descriptors->hash = $file->getHash();
			$descriptors->modifiedTime = $file->getModifiedTime();
			$descriptors->filesizeBytes = $file->getFileSizeInBytes();
			$descriptors->filesizeKBytes = $file->getFileSizeInKiloBytes();

			$fileHashes->set($file->getFilename(), $descriptors);
		}

		return $fileHashes;
	}
}