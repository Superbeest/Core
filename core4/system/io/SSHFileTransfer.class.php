<?php
/**
* SSHFileTransfer.class.php
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
* Implements the transferring of files via SFTP over SSH
* @package \System\IO
*/
class SSHFileTransfer extends \System\Base\Base implements \System\IO\iFileTransfer
{
	/**
	* @publicget
	* @publicset
	* @var string The host. This can be local or remote, or an IPv4
	*/
	protected $host;

	/**
	* @publicget
	* @publicset
	* @var int The port to use
	*/
	protected $port;

	/**
	* @publicget
	* @publicset
	* @var string The username for the connection
	*/
	protected $username;

	/**
	* @publicget
	* @publicset
	* @var string The password for the connection
	*/
	protected $password;

	/**
	* @publicset
	* @publicget
	* @var string The target folder
	*/
	protected $targetFile;

	/**
	* Creates a new Transfer object
	* @param string The host. This can be local or remote, or an IPv4
	* @param int The port to use
	* @param string The username for the connection
	* @param string The password for the connection
	* @param string The target filename, including fullpath
	*/
	public function __construct($host, $port, $username, $password,  $targetFile)
	{
		if ((!function_exists('ssh2_connect')) ||
			(!function_exists('ssh2_auth_password')) ||
			(!function_exists('ssh2_sftp')))
		{
			throw new \System\Error\Exception\SystemException('The required module, SSH2, is not loaded in PHP');
		}

		$this->setTargetFile($targetFile);
		$this->setHost($host);
		$this->setPort($port);
		$this->setUsername($username);
		$this->setPassword($password);
	}

	/**
	* Transfers the file over a connection
	* @param File The file to transfer
	* @return bool True on success, false otherwise
	*/
	public final function transferFile(\System\IO\File $file)
	{
		$connection = ssh2_connect($this->getHost(), $this->getPort());
		if (($connection) &&
			(ssh2_auth_password($connection, $this->getUsername(), $this->getPassword())))
		{
			$sftp = ssh2_sftp($connection);

			if (file_put_contents('ssh2.sftp://' . $sftp . $this->getTargetFile(), $file->getContents()) !== false)
			{
				ssh2_exec($connection, 'touch -d "' . $file->getModifiedTime()->toFullTime() . '" ' . $this->getTargetFile());
				return true;
			}
		}

		return false;
	}
}
