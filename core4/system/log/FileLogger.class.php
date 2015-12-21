<?php
/**
* FileLogger.class.php
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


namespace System\Log;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The implementation for the FileLogger. This system logs output to a temp file
* @package \System\Log
*/
class FileLogger extends \System\Log\BaseLogger implements \System\Log\iLogger
{
    /**
    * @var resource The handle to the file
    */
    protected $fileHandle;

	/**
	* @var string The logfile to use
	*/
    private $logFile;

	/**
	* The logfile level (oct 777 = int 511)
	*/
    const LOGFILE_ACCESSLEVEL = 0777;

	/**
	* Unknown process user
	*/
    const PROCESS_UNKNOWN_USER = 'Unknown';

    /**
    * The constructor for the FileLogger. This gets called automatically and is enforced by the singleton pattern.
    */
    protected final function __construct()
    {
		$processUsername = self::PROCESS_UNKNOWN_USER;
		if ((function_exists('posix_getpwuid')) &&
			(function_exists('posix_geteuid')))
		{
			$processUser = posix_getpwuid(posix_geteuid());
 			$processUsername = $processUser['name'];
		}

        $this->logFile = PATH_LOGS . SITE_IDENTIFIER . '_' . date('Y-m-d') . '.' . $processUsername . '.txt';

        $this->fileHandle = fopen($this->logFile, 'a');
        if (!$this->fileHandle)
        {
            throw new \System\Error\Exception\SystemException("Could not open LOG file: " . $logFile . "\r\n");
        }
    }

    /**
    * Destructs the open filehandles
    */
    public final function __destruct()
    {
    	$this->closeFile();
    }

	/**
	* Closes the file handle and sets the chmod to public changeable
	*/
    private final function closeFile()
    {
    	fclose($this->fileHandle);
    	chmod($this->logFile, self::LOGFILE_ACCESSLEVEL);
	}

	/**
	* Cleans the output file and resets it
	*/
    public final function flush()
    {
		$this->closeFile();
        $this->fileHandle = fopen($this->logFile, 'w');
	}

    /**
    * This functions outputs the message to the log
    * @param string The message to output to the given log.
    * @param integer The level of the logger
    */
    public final function out($message, $level = \System\Log\LoggerLevel::LEVEL_INFO)
    {
        $message = '[' . $this->levelToText($level) . '] [' . date("H:i:s") . '] ' . $message . "\r\n";

        if (fwrite($this->fileHandle, $message) === false)
        {
            throw new \System\Error\Exception\SystemException("Could not write to LOG file!\r\n");
        }

        if (fflush($this->fileHandle) === false)
        {
            throw new \System\Error\Exception\SystemException("Could not flush to LOG file!\r\n");
        }
    }
}