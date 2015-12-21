<?php
/**
* Upload.class.php
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


namespace System\HTTP\Storage;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Provides functionality to upload files through a POST instruction
* @package \System\HTTP\Storage
*/
class Upload extends \System\Base\StaticBase
{
    /**
    * @publicget
    * @publicset
    * @var array The allowed extensions for an upload.
    */
    protected $allowedExtensions = array('jpg', 'png', 'gif', 'jpeg', 'bmp');

    /**
    * @publicget
    * @publicset
    * @var int The amount of bytes set as the max upload filesize
    */
    protected $maxFileSize = \System\HTTP\Storage\FileSizes::FILESIZE_8M;

    /**
    * @publicget
    * @var string The name of the upload box.
    */
    protected $formFieldname = '';

    /**
    * @publicget
    * @publicset
    * @var string The target filename
    */
    protected $targetFilename = '';

    /**
    * @var \System\Collection\Vector The targets to write to
    */
    protected $targets = null;

    /**
    * Creates a new file upload and writes the data to the given targets.
    * The targets must be children of the FileTransfer class.
    * @param string The name of the upload box
    * @param string The target filename on the given targets
    * @param \System\Collection\Vector The targets to write to.
    */
    public function __construct($formFieldname, $targetFilename, \System\Collection\Vector $targets)
    {
        $this->formFieldname = $formFieldname;
        $this->targetFilename = $targetFilename;
        $this->targets = $targets;
    }

    /**
    * Checks if there are uploads available for processing.
    * @return bool True if there are uploads, false otherwise
    */
    public final function hasUploads()
    {
        return !empty($_FILES);
    }

    /**
    * Process the file upload and publish the posted data to the given targets.
    * Do note: this function returns True if all placements in the targets are succesfull.
    * @return bool True on success, false otherwise.
    */
    public final function process()
    {
        if (isset($_FILES[$this->formFieldname]))
        {
            if (is_uploaded_file($_FILES[$this->formFieldname]['tmp_name']))
            {
                //the file is uploaded succesfully

                //check for filesize
                if ($_FILES[$this->formFieldname]['size'] <= $this->maxFileSize)
                {
                    //check for fileextension
                    if (in_array(strtolower(pathinfo($_FILES[$this->formFieldname]['name'], PATHINFO_EXTENSION)), $this->allowedExtensions))
                    {
                        //no uploaderrors
                        if ($_FILES[$this->formFieldname]['error'] == UPLOAD_ERR_OK)
                        {
                            $fullPath = \System\IO\Directory::getPath(PATH_TEMP . $this->targetFilename);
                            if (@move_uploaded_file($_FILES[$this->formFieldname]['tmp_name'], $fullPath))
                            {
                                $file = new \System\IO\File($fullPath);

                                //cycle through all the targets and place them where desired
                                $success = true;
                                foreach ($this->targets as $target)
                                {
                                    if (!$target->transferFile($file))
                                    {
                                        $success = false;
                                    }
                                }

                                //remove the temporary file again
                                $file->delete();

                                return $success;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
    * Returns the filename of the object.
    * @return string The name of the filename or false on error
    */
    public final function getFilename()
    {
        if (isset($_FILES[$this->formFieldname]))
        {
            return pathinfo($_FILES[$this->formFieldname]['name'], PATHINFO_BASENAME);
        }

        return false;
    }
	
	/**
	* Returns the filename extension of the object
	* @return string The extension of the file or false on error
	*/
    public final function getFileExtension()
    {
		if (isset($_FILES[$this->formFieldname]))
        {
            return pathinfo($_FILES[$this->formFieldname]['name'], PATHINFO_EXTENSION);
        }

        return false;
	}

    /**
    * Returns the last upload error for this instance.
    * @return int The UPLOAD_ERR codes as defined by PHP
    */
    public final function getUploadError()
    {
        if (isset($_FILES[$this->formFieldname]))
        {
            return $_FILES[$this->formFieldname]['error'];
        }

        return UPLOAD_ERR_OK;
    }

    /**
    * Gets the current upload progress in percentages.
    * The apcId is the ID given from the GET variable in the APC_UPLOAD_PROGRESS field.
    * @param string The APC_UPLOAD_PROGRESS field value
    * @return int The completion percentage of the file upload
    */
    public static final function getUploadProgress($apcId)
    {
        if ((function_exists('apc_fetch')) &&
            (ini_get('apc.rfc1867')))
        {
            throw new \System\Error\Exception\SystemException('APC is not installed on this server. Please install the APC module and configure apc.rfc1867');
        }

        $prefix = ini_get('apc.rfc1867_prefix');

        //we bypass the systems APC cache, because that uses a site prefix in its keys.
        $status = apc_fetch($prefix . $apcId);
        if ($status)
        {
            return intval(($status['current'] / $status['total']) * 100);
        }

        return 0;
    }
}