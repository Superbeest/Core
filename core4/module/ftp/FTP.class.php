<?php
/**
* FTP.class.php
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


namespace Module\FTP;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Provides functionality to access FTP locations
* @package \Module\FTP
*/
class FTP extends \System\Base\Base implements \System\IO\iFileTransfer
{
    /**
    * @publicget
    * @var string The url of the FTP connection
    */
    protected $ftpURL;

    /**
    * @publicget
    * @var int The port to connect to.
    */
    protected $port;
    /**
    * The default FTP port
    */
    const PORT_DEFAULT = 21;

    /**
    * @var resource The actual connection holder
    */
    protected $connection = null;

    /**
    * Creates a new FTP connection.
    * @param string The host to connect to. This should be a fully qualified FTP URI.
    * @param string The username to use with the connection
    * @param string The password to use with the connection
    * @param string The port to connect to.
    */
    public function __construct($ftpURL, $username, $password, $port = \Module\FTP\FTP::PORT_DEFAULT)
    {
        $this->ftpURL = $ftpURL;
        $this->port = $port;

        $this->connection = ftp_connect($ftpURL, $port);
        if ($this->connection)
        {
            if (ftp_login($this->connection, $username, $password))
            {
                //we go to passive mode
                if (ftp_pasv($this->connection, true))
                {
                    return;
                }
                //close the connection upon failure
                $this->close();
            }
        }
        throw new \System\Error\Exception\SystemException('Could not connect to the given FTP with values: ' . $ftpURL . ':' . $port . ', ' . $username);
    }

    /**
    * Overrides the default string functionality with FTP information.
    * @return string The class information
    */
    public function __toString()
    {
        if ($this->connection)
        {
            return parent::__toString() . '(' . $this->ftpURL . ':' . $this->port . ')';
        }
        return parent::__toString();
    }

    /**
    * Gets the current working directory
    * @return string The current working directory, or false on failure
    */
    public function getCurrentDirectory()
    {
        if ($this->connection)
        {
            return ftp_pwd($this->connection);
        }

        return false;
    }

    /**
    * Deletes the given directory. The folder must be empty before deletion.
    * @param string The directory to delete.
    * @return bool true on success, false otherwise.
    */
    public function deleteDirectory($folder)
    {
        if ($this->connection)
        {
            return ftp_rmdir($this->connection, $folder);
        }

        return false;
    }

    /**
    * Deletes a file from the FTP in the current working directory (or given absolute path).
    * @param string The file to delete
    * @return bool true on success, false otherwise.
    */
    public function deleteFile($file)
    {
        if ($this->connection)
        {
            return ftp_delete($this->connection, $file);
        }

        return false;
    }

    /**
    * Destroys the object and closes the connection to the FTP.
    * This function is automatically called upon destruction of the object.
    */
    public function __destruct()
    {
        if ($this->connection)
        {
            $this->close();
        }
    }

    /**
    * Closes the connection to the FTP site.
    */
    public function close()
    {
        if ($this->connection)
        {
            ftp_close($this->connection);
            $this->connection = null;
        }
    }

    /**
    * Creates a new folder on the FTP. This will use the current working directory, unless an absolute path is given.
    * @param string The name of the folder to create.
    * @return string The name of the folder, or false on failure.
    */
    public function makekDirectory($name)
    {
        if ($this->connection)
        {
            return ftp_mkdir($this->connection, $name);
        }
        return false;
    }

    /**
    * Renames a folder or file.
    * @param string The old (current) name to rename.
    * @param string The new (target) name.
    * @return bool True on success, false otherwise
    */
    public function rename($previous, $new)
    {
        if ($this->connection)
        {
            return ftp_rename($this->connection, $previous, $new);
        }
        return false;
    }

    /**
    * Change the CHMOD value of the given file. Range 000-777
    * @param string The file to change the CHMOD of.
    * @param int The new CHMOd value
    * @return int The new permissions on success, false otherwise
    */
    public function setCHMOD($file, $value)
    {
        if ($this->connection)
        {
            return ftp_chmod($this->connection, $value, $file);
        }
        return false;
    }

    /**
    * Checks whether a file exists.
    * @param string The remote file to check
    * @return bool True on existing, false otherwise.
    */
    public function fileExists($remoteFile)
    {
        if ($this->connection)
        {
            return ($this->getFileSize($remoteFile) > 0);
        }

        return false;
    }

    /**
    * Returns the filesize in bytes.
    * @param string The filename
    * @return int The filesize in bytes, or -1 on failure
    */
    public function getFileSize($remoteFile)
    {
        if ($this->connection)
        {
            return ftp_size($this->connection, $remoteFile);
        }

        return -1;
    }

    /**
    * Gets a file from the FTP server.
    * @param string The remote file to get.
    * @param string The local file to write to.
    * @param int The FTP mode to use.
    * @return bool True on success, false otherwise.
    */
    public function getFile($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        if ($this->connection)
        {
            return ftp_get($this->connection, $localFile, $remoteFile, $mode);
        }
        return false;
    }

    /**
    * Uploads a file to the FTP
    * @param string The remote file to upload to.
    * @param string The local file to read from.
    * @param int The FTP mode to use.
    * @return bool True on success, false otherwise.
    */
    public function putBlocking($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        if ($this->connection)
        {
            return ftp_put($this->connection, $remoteFile, $localFile, $mode);
        }

        return false;
    }

    /**
    * Set PASV mode on or off.
    * @param bool True for PASV, false otherwise
    * @return bool True on success, false otherwise.
    */
    public function pasv($pasv = true)
    {
        if ($this->connection)
        {
            return ftp_pasv($this->connection, $pasv);
        }

        return false;
    }

    /**
    * Sets the current working directory.
    * @param string The new working directory
    * @return bool True on success, false otherwise.
    */
    public function setCurrentWorkingDirectory($folder)
    {
        if ($this->connection)
        {
            return ftp_chdir($this->connection, $folder);
        }

        return false;
    }

    /**
    * Gets a file listing.
    * @return array The list of files, or an empty array on no files.
    */
    public function getList()
    {
        if ($this->connection)
        {
            return ftp_nlist($this->connection, '.');
        }
        return array();
    }

    /**
    * Gets a extended file listing.
    * @return array The list of files, or an empty array on no files.
    */
    public function getRawList()
    {
        if ($this->connection)
        {
            return ftp_rawlist($this->connection, '.');
        }
        return array();
    }

    /**
    * Returns a Time object with the last file modification time.
    * @param string The file to check
    * @return \System\Calendar\Time A Time object, or null on failure.
    */
    public function getFileModifiedDate($file)
    {
        if ($this->connection)
        {
            $answer = ftp_raw($this->connection, 'MDTM ' . $file);
            if ((count($answer) == 1) &&
                (substr($answer[0], 0, 3) == '213'))
            {
                $answerDate = substr($answer[0], 4);
                $year = substr($answerDate, 0, 4);
                $month = substr($answerDate, 4, 2);
                $day = substr($answerDate, 6, 2);
                $hour = substr($answerDate, 8, 2);
                $minute = substr($answerDate, 10, 2);
                $second = substr($answerDate, 12, 2);
                $time = new \System\Calendar\Time(strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second));
                return $time;
            }
        }

        return null;
    }

    /**
    * Transfer the file to its new location by copying it.
    * @param \System\IO\File The sourcefile.
    * @return bool True on success, false otherwise
    */
    public final function transferFile(\System\IO\File $file)
    {
        return $this->putBlocking($file->getFilename(), $file->getFullPath());
    }
}
