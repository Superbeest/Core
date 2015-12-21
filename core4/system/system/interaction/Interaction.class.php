<?php
/**
* Interaction.class.php
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
* Implements the Interaction interface
* @package \System\System\Interaction
*/
class Interaction extends \System\Base\StaticBase
{
	/**
	* Defines the key to use for communication encryption
	*/
	const INTERACTION_COMMUNICATION_KEY = 'I like cookies! ^_^';

	/**
	* Defines the useragent
	*/
	const INTERACTION_USERAGENT = 'Agent SuperHolder';

	/**
	* Defines the target url to call. Will be postfixed at the root
	*/
	const INTERACTION_CONTROLLER_CALL = 'system/interactionrcv/interact';

	/**
	* The default timeout for a synchronous call
	*/
	const INTERACTION_CALL_TIMEOUT = 60;

	/**
	* Sends a message to the given remote host via a HTTP POST request. The remote host must have the System module activated in order to process the request.
	* Optional listeners should be attached on the remote host for the OnInteractionEvent event.
	* @param string The remote root url. This should only be the root, ending with a '/'
	* @param \System\Collection\Vector A vector with \System\System\Interaction\Message obejcts
	* @param string The custom encryptionkey. This is used over the systems default key
	* @return \System\Collection\Vector A Vector with \System\System\Interaction\Response objects, or false on error or no reply
	*/
	public static final function sendMessage($rootUrl, \System\Collection\Vector $messages, $customEncryptionKey = '')
	{
		$targetUrl = $rootUrl . self::INTERACTION_CONTROLLER_CALL;

		$encodedMessages = self::encode($messages, $customEncryptionKey);
		$paramMap = new \System\Collection\Map();
		$paramMap['request'] = $encodedMessages;

		$request = \System\HTTP\Request\Request::getRequest();

		$a1 = array();
		$a2 = array();

		$response = \System\HTTP\Request\Call::httpPageRequest($targetUrl, $paramMap, self::INTERACTION_USERAGENT, $request, '', $a1, $a2, self::INTERACTION_CALL_TIMEOUT);
		if ($response)
		{
			$decodedResponse = self::decode($response, $customEncryptionKey);

			return $decodedResponse;
		}

		return false;
	}

	/**
	* Encodes the given messages to a transferable string.
	* @param \System\Collection\Vector A Vector with \System\System\Interaction\Message or \System\System\Interaction\Response objects.
	* @param string The custom encryptionkey. This is used over the systems default key
	* @return string The encoded string
	*/
	public static final function encode(\System\Collection\Vector $messages, $customEncryptionKey = '')
	{
		$messagesToSend = array();

		foreach ($messages as $message)
		{
			$messagesToSend[] = serialize($message);
		}

		$messageToSend = json_encode($messagesToSend);

		$encodedMessage = \System\Security\AESEncoding::encode($messageToSend, $customEncryptionKey ?: self::getEncryptionKey(), \System\Security\AESEncoding::CYPHER_256);
		return $encodedMessage;
	}

	/**
	* Returns the current set interaction communication key.
	* The key can be set using a config directive by declaring INTERACTION_COMMUNICATION_KEY using define();
	* If that constant is not defined, then the local class constant INTERACTION_COMMUNICATION_KEY gets used instead.
	* Do note that this variable is public.
	* @return string The INTERACTION_COMMUNICATION_KEY value
	*/
	private static function getEncryptionKey()
	{
		if (defined('INTERACTION_COMMUNICATION_KEY'))
		{
			return constant('INTERACTION_COMMUNICATION_KEY');
		}
		return self::INTERACTION_COMMUNICATION_KEY;
	}

	/**
	* Decodes the given encoded message and converts it into a vector with \System\System\Interaction\Message or \System\System\Interaction\Response objects.
	* @param string The encoded message.
	* @param string The custom encryptionkey. This is used over the systems default key
	* @return \System\Collection\Vector A Vector with \System\System\Interaction\Message or \System\System\Interaction\Response objects
	*/
	public static final function decode($inputString, $customEncryptionKey = '')
	{
		$decodedMessage = \System\Security\AESEncoding::decode($inputString, $customEncryptionKey ?: self::getEncryptionKey(), \System\Security\AESEncoding::CYPHER_256);
		$decodedArray = json_decode($decodedMessage);

		if ($decodedArray)
		{
			$messages = new \System\Collection\Vector();
			foreach ($decodedArray as $message)
			{
				$messages[] = unserialize($message);
			}

			return $messages;
		}

		return false;
	}
}