<?php
/**
* FileSystemTransfer.class.php
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
* Copies a file from the given location to a new location on a local filesystem
* @package \System\IO
*/
class FileSystemTransfer extends \System\Base\Base implements \System\IO\iFileTransfer
{
    /**
    * @publicset
    * @publicget
    * @var string The full path to place the
    */
    protected $targetFolder = PATH_TEMP;

    /**
    * Creates a new Transfer object
    * @param string The target folder to write to
    */
    public function __construct($targetFolder)
    {
        $this->setTargetFolder($targetFolder);
    }

    /**
    * Transfer the file to its new location by copying it.
    * @param \System\IO\File The sourcefile.
    * @return bool True on success, false otherwise
    */
    public final function transferFile(\System\IO\File $file)
    {
        $target = \System\IO\Directory::getPath($this->targetFolder . $file->getFilename());
        return (\System\IO\File::writeContents($target, $file->getContents()) instanceof \System\IO\File);
    }
}