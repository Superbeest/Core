<?php
/**
* SystemInteractionEvent.event.php
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


namespace System\System\Interaction\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements system module level event listeners
* @package \System\System\Interaction\Event
*/
final class SystemInteractionEvent extends \System\Base\StaticBase
{
	/**
	* Registers the system event after the module gets loaded
	*/
	public static final function registerListeners()
	{
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::query');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::cleanPageCache');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::databaseFingerprint');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::fileFingerprint');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::receiveFile');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::sendFile');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::deleteFile');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::shellExec');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::getLoadedModules');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::getConstant');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::invalidateStaticCacheEntry');
		\System\System\Interaction\Event\OnInteractionEvent::register('\System\System\Interaction\Event\SystemInteractionEvent::opcacheReset');
	}

	/**
	* Allows to clear the internal php opcache. Listens to EVENT_OPCACHE_RESET
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function opcacheReset(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_OPCACHE_RESET))
		{
			$reset = opcache_reset();

			$response = new \System\System\Interaction\Response($msg, $reset ? 'OK' : 'Error');
			$event->addResponse($response);
		}
	}

	/**
	* Allows to invalidate a specific static cache entry. Listens to EVENT_INVALIDATE_STATICCACHE_ENTRY and requires 'entry'
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function invalidateStaticCacheEntry(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_INVALIDATE_STATICCACHE_ENTRY))
		{
			$params = $msg->getParams();

			if (isset($params['entry']))
			{
				\System\Cache\PageCache\StaticBlock::invalidate($event->getDatabase(), $params['entry']);
				$response = new \System\System\Interaction\Response($msg, 'Entry: ' . $params['entry'] . ' cleared');
				$event->addResponse($response);
			}
		}
	}

	/**
	* Gets the value of the given constant. Listens to the EVENT_GET_CONSTANT message. Requires the 'constant' parameter.
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function getConstant(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_GET_CONSTANT))
		{
			$params = $msg->getParams();

			if ((isset($params['constant'])) &&
				(defined($params['constant'])))
			{
				$response = new \System\System\Interaction\Response($msg, constant($params['constant']));
				$event->addResponse($response);
			}
		}
	}

	/**
	* Gets all the loaded modules. Listens to the EVENT_GET_LOADED_MODULES message
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function getLoadedModules(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_GET_LOADED_MODULES))
		{
			$response = new \System\System\Interaction\Response($msg, \System\Module\Module::getAllModules());
			$event->addResponse($response);
		}
	}

	/**
	* Allows to execute a shell command and returns the output. Listens to EVENT_SHELL_EXEC and requires 'command'
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function shellExec(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_SHELL_EXEC))
		{
			$params = $msg->getParams();

			if (isset($params['command']))
			{
				$output = shell_exec($params['command']);
				if ($output)
				{
					$response = new \System\System\Interaction\Response($msg, $output);
					$event->addResponse($response);
				}
			}
		}
	}

	/**
	* Deletes a file from the filesystem. Listens to EVENT_DELETE_FILE and requires a 'host', 'port', 'username', 'password' and 'file' field.
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function deleteFile(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_DELETE_FILE))
		{
			$params = $msg->getParams();

			if ((isset($params['file'])) &&
				(isset($params['host'])) &&
				(isset($params['username'])) &&
				(isset($params['password'])) &&
				(isset($params['port'])))
			{
				//test if the file exists
				$file = new \System\IO\File($params['file']);

				if ((!function_exists('ssh2_connect')) ||
					(!function_exists('ssh2_auth_password')) ||
					(!function_exists('ssh2_sftp')))
				{
					throw new \System\Error\Exception\SystemException('The required module, SSH2, is not loaded in PHP');
				}

				$connection = ssh2_connect($params['host'], $params['port']);
				if (($connection) &&
					(ssh2_auth_password($connection, $params['username'], $params['password'])))
				{
					$sftp = ssh2_sftp($connection);
					$result = ssh2_sftp_unlink($sftp, getcwd() . '/' . $params['file']);

					$response = new \System\System\Interaction\Response($msg, 'File delete ' . $file->getFilename() . ': ' . ($result ? 'success' : 'fail'));
					$event->addResponse($response);
				}
			}
		}
	}

	/**
	* Returns a file from the local filesystem and sends it as a response. Listens to EVENT_SEND_FILE and requires a 'file' param
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function sendFile(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_SEND_FILE))
		{
			$params = $msg->getParams();

			$file = new \System\IO\File($params['file']);

			$response = new \System\System\Interaction\FileResponse($msg, $file);
			$event->addResponse($response);
		}
	}

	/**
	* Handles the receiving of remote files and places them on the correct location. Listens to the EVENT_RECEIVE_FILE message
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function receiveFile(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_RECEIVE_FILE) &&
			($msg instanceof \System\System\Interaction\FileMessage))
		{
			$params = $msg->getParams();
			$targetFile = getcwd() . '/' . $params['target'];
			$tempFileName = PATH_TEMP . uniqid('sshtransfer');
			$file = \System\IO\File::writeContents($tempFileName, $msg->getFileData());
			$file->touch($params['mtime'], $params['atime']);

			$transfer = new \System\IO\SSHFileTransfer($params['host'], $params['port'], $params['username'], $params['password'], $targetFile);
			if ($transfer->transferFile($file))
			{
				$response = new \System\System\Interaction\Response($msg, $targetFile . ' (' . $file->getFileSizeInBytes() . 'b)');
				$file->delete();
				$event->addResponse($response);
			}
		}
	}

	/**
	* Listens to the EVENT_DB_FINGERPRINT event and returns the fingerprint of the current active default database
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function databaseFingerprint(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_DB_FINGERPRINT))
		{
			$db = $event->getDatabase();

			$hashes = \System\System\Introspection\Database::getTableHashes($db);

			$response = new \System\System\Interaction\Response($msg, $hashes);
			$event->addResponse($response);
		}
	}

	/**
	* Listens to the EVENT_FILE_FINGERPRINT event and returns the fingerprint of the given folders
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function fileFingerprint(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_FILE_FINGERPRINT))
		{
			$params = $msg->getParams();

			if (isset($params['folders']))
			{
				$folders = $params['folders'];

				$map = new \System\Collection\Map();

				foreach ($folders as $folder)
				{
					$directory = new \System\IO\Directory($folder, false);

					if ($directory)
					{
						$content = \System\System\Introspection\IO::getFileFingerprint($directory);

						$subFolders = new \System\Collection\Vector();
						foreach ($directory->getDirectories() as $subFolder)
						{
							/** @var \System\IO\Directory */
							$subFolder = $subFolder;
							$subFolders[] = \System\IO\Directory::getPath($subFolder->getCurrentPath(true), '/');
						}
						$content->set('folders', $subFolders);

						$map->set($folder, $content);
					}
				}

				$response = new \System\System\Interaction\Response($msg, $map);
				$event->addResponse($response);
			}
		}
	}

	/**
	* Listens for the EVENT_QUERY event for the system.
	* @param OnInteractionEvent The event to listen to.
	*/
	public static final function query(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_QUERY))
		{
			$db = $event->getDatabase();

			$params = $msg->getParams();

			$query = $params['query'];

			$q = new \System\Db\Query($db, $query);
			$results = new \System\Collection\Vector($db->query($q));

			$response = new \System\System\Interaction\Response($msg, $results);
			$event->addResponse($response);
		}
	}

	/**
	* Listens to the EVENT_CLEAN_PAGECACHE event and cleans the page cache files
	* @param OnInteractionEvent The event to listen to
	*/
	public static final function cleanPageCache(\System\System\Interaction\Event\OnInteractionEvent $event)
	{
		$msg = $event->getMessage();

		if (($msg->getType() == \System\System\Interaction\MessageType::TYPE_SYSTEM) &&
			($msg->getMessage() == SystemInteractionEventEvent::EVENT_CLEAN_PAGECACHE))
		{
			$db = $event->getDatabase();

			\System\Cache\PageCache\Page::cleanPageCache($db);

			$response = new \System\System\Interaction\Response($msg, 'PAGE CACHE CLEANED');
			$event->addResponse($response);
		}
	}
}