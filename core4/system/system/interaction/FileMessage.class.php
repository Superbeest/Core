<?php
/**
* FileMessage.class.php
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


namespace System\System\Interaction;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements a specialized version of the Message
* @package \System\System\Interaction
*/
class FileMessage extends Message
{
	/**
	* Creates a new Message object with a file.
	* @param \System\IO\File The file to send in the message
	* @param string The target filename. This is a fullpath
	* @param string The host. This can be local or remote, or an IPv4
	* @param int The port to use
	* @param string The username to connect to the local SSH server
	* @param string The password to connect to the local SSH server
	*/
	public function __construct_6(\System\IO\File $file, $targetFilename, $host, $port, $username, $password)
	{
		$this->setType(\System\System\Interaction\MessageType::TYPE_SYSTEM);
		$this->setMessage('file');

		$contents = @base64_encode($file->getContents());

		$this->setParams(array(
			'file' => $contents,
			'atime' => $file->getLastAccessTime(),
			'mtime' => $file->getModifiedTime(),
			'host' => $host,
			'port' => $port,
			'username' => $username,
			'password' => $password,
			'target' => $targetFilename)
		);
	}

	/**
	* Returns a (binary) string with file data.
	* This data is not encapsulated in a File object, as it does not represent a physical file.
	* @return string The (binary) file data, or false on failure
	*/
	public final function getFileData()
	{
		$params = $this->getParams();
		if ((isset($params['file'])) &&
			(isset($params['atime'])) &&
			(isset($params['mtime'])) &&
			(isset($params['host'])) &&
			(isset($params['port'])) &&
			(isset($params['username'])) &&
			(isset($params['password'])) &&
			(isset($params['target'])))
		{
			return @base64_decode($params['file']);
		}

		return false;
	}
}