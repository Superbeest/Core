<?php
/**
* File.class.php
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
* A class that represents a basic file
* @package \System\IO
*/
class File extends \System\Base\Base
{
    /**
    * @publicget
    * The filename of the file, this is the basename, including the extension
    */
    protected $filename = '';
    /**
    * @publicget
    * @var string The fullpath of the file, including its basename and extension
    */
    protected $fullPath = '';

    /**
    * Creates a new File object
    * @param string The full path of the file
    * @param boolean Throws an exception if the file does not exists, or silentignore when false
    */
    public final function __construct($fullPath, $exceptionOnNotExists = true)
    {
        $this->fullPath = \System\IO\Directory::getPath($fullPath);
        $this->filename = basename($this->fullPath);

        if (($exceptionOnNotExists) &&
            (!file_exists($fullPath)))
        {
            throw new \Exception('file not found: ' . $fullPath);
        }
    }

    /**
    * Returns the name of the file, excluding path, excluding extension
    * @return string The name of the file excluding path and extension.
    */
    public final function getBaseFilename()
    {
    	return basename($this->fullPath, '.' . $this->getExtensionOriginal());
	}

    /**
    * Returns the filesize in bytes. This function is limited to 2GB
    * @return int the filesize in bytes
    */
    public final function getFileSizeInBytes()
    {
        return filesize($this->fullPath);
    }

    /**
    * Gets the contents of the file as a string
    * @return string The contents of the file
    */
    public final function getContents()
    {
        return file_get_contents($this->fullPath);
    }

    /**
    * Returns the filesize in bytes. This function is limited to 4GB
    * and is slower than the getFileSizeInBytes() function. When using
    * smaller files, the latter is advised.
    * @return string the filesize in bytes as a string
    */
    public final function getBigFileSizeInBytes()
    {
        return sprintf("%u", filesize($this->fullPath));
    }

	/**
	* Sets both the access time and the modified time of the file
	* @param \System\Calendar\Time The modified time.
	* @param \System\Calendar\Time The access time
	* @return File The current instance
	*/
    public final function touch(\System\Calendar\Time $modifiedTime, \System\Calendar\Time $accessTime)
    {
    	touch($this->getAbsoluteFilename(), $modifiedTime->toUNIX(), $accessTime->toUNIX());
    	return $this;
	}

	/**
	* Sets the access time of the file
	* @param \System\Calendar\Time The access time
	* @return File The current instance
	*/
    public final function setAccessTime(\System\Calendar\Time $time)
    {
		touch($this->getAbsoluteFilename(), filemtime($this->fullPath), $time->toUNIX());
    	return $this;
	}

	/**
	* Sets the modified time of the file
	* @param \System\Calendar\Time The modified time
	* @return File The current instance
	*/
	public final function setModifiedTime(\System\Calendar\Time $time)
	{
		touch($this->getAbsoluteFilename(), $time->toUNIX(), fileatime($this->fullPath));
    	return $this;
	}

    /**
    * Write content to a file and returns a File object. The file will be overwritten if it exists.
    * @param string The fullpath to the file.
    * @param string The content to write to the file
    * @return \System\IO\File A File object or null on failure
    */
    public static final function writeContents($fullPath, $content)
    {
        if (file_put_contents($fullPath, $content))
        {
            return new File($fullPath);
        }

        return null;
    }

    /**
    * Returns the filesize in kilobytes. This function is limited to 2GB
    * @return string the filesize in kilobytes, rounded on two digits and appended by 'KB'
    */
    public final function getFileSizeInKiloBytes()
    {
        return number_format(($this->getFileSizeInBytes() / 1024), 2) . 'KB';
    }

    /**
    * Returns the filesize in megabytes. This function is limited to 2GB
    * @return string the filesize in megabytes, rounded on two digits and appended by 'KB'
    */
    public final function getFileSizeInMegaBytes()
    {
        return number_format(($this->getFileSizeInBytes() / 1024 / 1024), 2) . 'MB';
    }

	/**
    * Returns the extension string of the current file. This usually has a length of {1,3}.
    * Note: the (.) point sign is excluded of the extension.
    * @return string the extension of the file.
    */
    public final function getExtensionOriginal()
    {
    	//because of parameter passing by reference, we need a helper variable here
        $y = explode('.', $this->filename);
        return end($y);
	}

    /**
    * Returns the extension string of the current file. This usually has a length of {1,3}.
    * Note: the (.) point sign is excluded of the extension. And the extension is returned as lowercase.
    * @return string the extension of the file.
    */
    public final function getExtension()
    {
        return mb_strtolower($this->getExtensionOriginal());
    }

    /**
    * Returns a Time object representing the last modification time of the file
    * @return \System\Calendar\Time A time object
    */
    public final function getModifiedTime()
    {
        return new \System\Calendar\Time(filemtime($this->fullPath));
    }

	/**
	* Returns a MD5 hash for the contents of this file.
	* @return string The hash of the current file instance
	*/
    public final function getHash()
    {
    	return hash_file('md5', $this->getFullPath(), false);
	}

    /**
    * Returns a Time object representing the last access time of the file.
    * @return \System\Calendar\Time A Time object
    */
    public final function getLastAccessTime()
    {
        return new \System\Calendar\Time(fileatime($this->fullPath));
    }

    /**
    * Returns the path of the file, exclusing the filename, including trailing slash
    * @return string The path of the file
    */
    public final function getPath()
    {
        return mb_substr($this->fullPath, 0, -mb_strlen($this->getFilename()));
    }

	/**
	* Copies the current file to another location
	* @param string The new full target
	*/
    public final function copyFile($target)
    {
    	copy($this->getFullPath(), $target);
	}

    /**
    * Checks if the file still exists
    * @return bool Returns true if the file still exists, false otherwise.
    */
    public final function exists()
    {
        return file_exists($this->fullPath);
    }

	/**
	* Returns the fulename, including its extension, including its folder.
	* @return string The filename of the file.
	*/
    public final function getAbsoluteFilename()
    {
    	return $this->fullPath;
	}

    /**
    * Deletes the current file from disc. After this operation, the file will become unusable.
    * @return bool True on success, false otherwise.
    */
    public final function delete()
    {
        return unlink($this->getFullPath());
    }

	/**
	* Rename or move the current file.
	* Requires a full valid path to move to
	* @param string The new full path.
	* @return File The new File instance, or false on failure
	*/
    public final function move($newFullPath)
    {
    	if (rename($this->getAbsoluteFilename(), $newFullPath))
    	{
    		return new File($newFullPath);
		}

		return false;
	}

    /**
    * This function is identical to the getFullPath() function, except that it strips the given
    * $base variable from the beginning of the file. If the given base is not found, it returns the regular getFullPath() result.
    * The base variable gets normalised before checking.
    * @param string The base to remove from the beginning
    * @return string The remaining fullpath string
    */
    public final function stripBase($base)
    {
        $path = $this->getFullPath();
        $base = \System\IO\Directory::normalize($base) ?: \System\IO\Directory::getPath($base);

        $startPos = stripos($path, $base);
        if (($startPos !== false) &&
            ($startPos == 0))
        {
            return substr($path, strlen($base));
        }

        return $path;
    }

    /**
    * Returns the MimeType of the file. This is based on a lut and the extension of the file.
    * Other methods could be applied, but result in slower retrieval of the mimetype.
    * The mimetype is returned as a string.
    * @returns string The MIME type of the file, or 'application/octet-stream' for unknown
    */
    public final function getMimeType()
    {
        if (file_exists($this->getFullPath()))
        {
            $mimeTypes = array(
                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',
                'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
                'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
                'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
                'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
                'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
                'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet');

            if (array_key_exists($this->getExtension(), $mimeTypes))
            {
                return $mimeTypes[$this->getExtension()];
            }
            else
            {
                return 'application/octet-stream';
            }
        }
        else
        {
            throw new \Exception('file could not be opened ' . $this->getFullPath());
        }
    }
}
