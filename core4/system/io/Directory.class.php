<?php
/**
* Directory.class.php
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
* This container class handles directory access and information about directories
* @package \System\IO
*/
class Directory extends \System\Base\Base
{
	/**
	* Octal value for full access
	*/
	const FULL_ACCESS = 0777;

    /**
    * The path to work with
    */
    protected $path = '';

    /**
    * Returns the separator used in directories for the running platform
    * @return string The separator string used in the current running platform
    */
    public static final function getSeparator()
    {
        return DIRECTORY_SEPARATOR;
    }

    /**
    * Returns a transformed path, suitable for the current OS.
    * Also fixes double slashes to single slashes.
    * @param string The path to convert for the current OS.
    * @param string The separator to use. When null, the system default is used.
    * @return string Returns the path, suitable for the current OS.
    */
    public static final function getPath($path, $separator = null)
    {
        $slash = self::getSeparator();
        if ($separator != null)
        {
            $slash = $separator;
        }
        $path = str_replace('\\\\', '\\', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace("/", $slash, $path);
        $path = str_ireplace("\\", $slash, $path);

        return $path;
    }

    /**
    * Returns a transformed path, suitable for the current OS.
    * Also fixes double slashes to single slashes, and validates the path.
    * Default adds a trailing slash.
    * @param string The path to convert for the current OS.
    * @param string The separator to use. When null, the system default is used.
    * @return string Returns the path, suitable for the current OS, or false.
    */
    public static final function normalize($path, $separator = null)
    {
        $slash = self::getSeparator();
        if ($separator != null)
        {
            $slash = $separator;
        }

        $path = realpath($path);
        if (!$path)
        {
            return false;
        }

        $path .= $slash;

        $path = str_replace('\\\\', '\\', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace("/", $slash, $path);
        $path = str_ireplace("\\", $slash, $path);

        return $path;
    }

    /**
    * Creates a new directory on disk.
    * The rights given are octal. The directory will be created recursively.
    * @param string The path to be given
    * @param int Octal rights
    * @return Directory The new directory or false on failure
    */
    public static function create($path, $rights = 0777)
    {
    	if (mkdir($path, $rights, true))
    	{
    		return new Directory($path, true);
		}

		return false;
	}

    /**
    * Recursively walk through the given path and list all its files and/or Directories
    * The given parameter will be automatically converted to a valid OS path.
    * This function automatically excludes .svn folders and .htaccess/.htpasswd files.
    * When the second parameter is given, this function also checks for extensions.
    * Only extensions in the second parameter will be added to the finite listing.
    * We use inclusion for this, because exclusion implies we can also include potentially
    * unwanted files, and this might pose a security risc.
    * If the second extension is not given, all files will be listed (with exclusion of .svn etc).
    * @param string The directory to walk through.
    * @param \System\Collection\Vector extensions to include in the search.
    * @param int A \System\IO\DirectoryWalkDirInfo bitstruct indicating the required information
    * @return \System\Collection\Vector A collection containing File and/or Directory objects
    */
    public static final function walkDir($path, \System\Collection\Vector $extensions = null, $info = \System\IO\DirectoryWalkDirInfo::INFO_FILES)
    {
        //remove the trailing slash so the system can properly walk the path
        $path = self::removeTrailingSeparator($path);

        //we should iterate a path
        if (is_dir($path))
		{
    		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::CURRENT_AS_FILEINFO | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

			$list = new \System\Collection\Vector();

			$infoTypes = new DirectoryWalkDirInfo();

    		foreach ($iterator as $directoryItem)
		    {
				/** @var \SplFileInfo */
				$directoryItem = $directoryItem;

				if (mb_substr($directoryItem->getBasename(), 0, 1) != '.')
				{
					if ((\System\IO\DirectoryWalkDirInfo::contains($info, DirectoryWalkDirInfo::INFO_FILES)) &&
						($directoryItem->isFile()))
					{
						$file = new \System\IO\File($directoryItem->getRealPath());
						if ($extensions != null)
	                    {
	                        if ($extensions->contains($file->getExtension()))
	                        {
	                            $list[] = $file;
	                        }
	                    }
	                    else
	                    {
	                        $list[] = $file;
	                    }
					}

					if ((\System\IO\DirectoryWalkDirInfo::contains($info, DirectoryWalkDirInfo::INFO_DIRECTORIES)) &&
						($directoryItem->isDir()))
					{
						$folder = new \System\IO\Directory($directoryItem->getRealPath());
						$list[] = $folder;
					}
				}
			}

			return $list;
		}

        return new \System\Collection\Vector();
    }

    /**
    * Removes the trailing separator from the given path. By doing so, the system will
    * be able to walk the path.
    * This function also converts the path to a valid separator path.
    * @param string The path to remove the trailing separator from
    * @return string The newly given path without trailing separator
    */
    protected static final function removeTrailingSeparator($path)
    {
        $path = self::getPath($path);

        if (mb_substr($path, 0, -1) == self::getSeparator())
        {
            $path = mb_substr($path, 0, -1);
        }

        return $path;
    }

    /**
    * Creates a new Directory object from the given path
    * The path may not be empty.
    * @param string The path to work with.
    * @param boolean Throws an exception if the path does not exists, or silentignore when false
    */
    public final function __construct($path, $exceptionOnNotExists = true)
    {
    	//remove the trailing slash so the system can properly walk the path
    	$this->path = self::removeTrailingSeparator($path);

        if ((!file_exists($path)) ||
            (!is_dir($path)))
        {
            if ($exceptionOnNotExists)
	        {
	            throw new \System\Error\Exception\InvalidArgumentException('given path does not exist, or is not a directory: ' . $path);
	        }
        }
    }

    /**
    * Checks if the folder still exists
    * @return bool Returns true if the folder still exists, false otherwise.
    */
    public final function exists()
    {
        return ((file_exists($this->path)) && (is_dir($this->path)));
    }

	/**
	* Clears out a directory recursivly.
	* Removes folders and files.
	* @return bool Returns true on success, false otherwise
	*/
    public final function clear()
    {
		if ($this->exists())
		{
			$contents = scandir($this->path);
			foreach ($contents as $content)
			{
				if (($content != '.') &&
					($content != '..'))
				{
					$fullContentEntry = $this->path . self::getSeparator() . $content;
					switch (filetype($fullContentEntry))
					{
						case FileType::FILETYPE_DIR:
							$folder = new \System\IO\Directory($fullContentEntry);
							$folder->clear();
							break;
						case FileType::FILETYPE_LINK:
						case FileType::FILETYPE_FILE:
							unlink($fullContentEntry);
							break;
						default:
							throw new \System\Error\Exception\FileNotFoundException($fullContentEntry . ' is not recognized as a file or a folder');
					}
				}
			}
			return rmdir($this->path);
		}
		return false;
	}

    /**
    * Gets all the files in the current directory, with exclusion of
    * .svn, .htaccess, .htpasswd etc.
    * Does not iterate to deeper levels.
    * @param \System\Collection\Vector extensions to include in the search.
    * @return \System\Collection\Vector A Vector with File objects.
    */
    public final function getFiles(\System\Collection\Vector $extensions = null)
    {
        $list = new \System\Collection\Vector();

         //list all the files
        $handle = opendir($this->path);

        while (($file = readdir($handle)) !== false)
        {
            //get the full path
            $filePath = self::getPath($this->path . self::getSeparator() . $file);

            //no current or upper folders should be considered and the target should exist.
            if (($file != '.') &&
                ($file != '..') &&
                (mb_substr($file, 0, 1) != '.') && //we dont want to include svn or htaccess files
                (file_exists($filePath)) &&
                (!is_dir($filePath)))
            {
                $file = new \System\IO\File($filePath);
                if ($extensions != null)
                {
                    if ($extensions->contains($file->getExtension()))
                    {
                        $list[] = $file;
                    }
                }
                else
                {
                    $list[] = $file;
                }
            }
        }

        closedir($handle);

        return $list;
    }

    /**
    * Gets all the Directories in the current directory, with exclusion of current and higher.
    * Directories starting with a (.) point will be ignored.
    * Does not iterate to deeper levels.
    * @return \System\Collection\Vector A Vector with Directory objects.
    */
    public final function getDirectories()
    {
        $list = new \System\Collection\Vector();

         //list all the files
        $handle = opendir($this->path);

        while (($file = readdir($handle)) !== false)
        {
            //get the full path
            $filePath = self::getPath($this->path . self::getSeparator() . $file);

            //no current or upper folders should be considered and the target should exist.
            if (($file != '.') &&
                ($file != '..') &&
                (mb_substr($file, 0, 1) != '.') && //we dont want to include svn or htaccess files
                (file_exists($filePath)) &&
                (is_dir($filePath)))
            {
                $directory = new \System\IO\Directory($filePath);
                $list[] = $directory;
            }
        }

        closedir($handle);

        return $list;
    }

    /**
    * Returns the current path of the object.
    * @param bool True to add a trailing separator, false otherwise
    * @return string The current path.
    */
    public final function getCurrentPath($addTrailingSeparator = false)
    {
		if (!$addTrailingSeparator)
		{
        	return $this->path;
		}
		return self::getPath($this->path . self::getSeparator());
    }
}
