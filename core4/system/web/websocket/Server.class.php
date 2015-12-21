<?php
/**
* Server.class.php
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


namespace System\Web\Websocket;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the abstract websocketserver
* @package \System\Web\Websocket
*/
abstract class Server extends \System\Base\Base
{
	/**
	* The default port to use for the webserver
	*/
	const PORT_DEFAULT = 9000;

	/**
	* The default length of the readbuffer
	*/
	const BUFFERLENGTH_DEFAULT = 2048;

	/**
	* The default size of the backlog with concurrent connection
	*/
	const BACKLOGAMOUNT_DEFAULT = 20;

	/**
	* The key for the master socket in the socket array
	*/
	const MASTERSOCKET_KEY = 'MasterSocket';

	/**
	* The websocket GUID as defined by the RFC
	*/
	const WEBSOCKET_GUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

	const HTTP_METHOD_NOT_ALLOWED = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";
	const HTTP_BAD_REQUEST = "HTTP/1.1 400 Bad Request";
	const HTTP_UPGRADE_REQUIRED = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
	const HTTP_FORBIDDEN = "HTTP/1.1 403 Forbidden";

	/**
	* @var int Holds the read buffer size
	*/
	private $maximumBufferSize = self::BUFFERLENGTH_DEFAULT;

	/**
	* @var resource The primary socket for the server
	*/
	private $masterSocket;

	/**
	* @var array The array that holds all the connections
	*/
	private $sockets = array();

	/**
	* @var array The array that holds all the connections
	*/
	private $connections = array();

	/**
	* @publicget
	* @publicset
	* @var bool True if the origin header is required
	*/
	protected $headerOriginRequired = false;

	/**
	* @publicget
	* @publicset
	* @var bool True if the secwebsocketprotocol header is required
	*/
  	protected $headerSecWebSocketProtocolRequired = false;

  	/**
  	* @publicget
  	* @publicset
  	* @var bool True if the secwebsocketextensions header is required
  	*/
  	protected $headerSecWebSocketExtensionsRequired = false;

	/**
	* Creates an instance of the webserver
	* @param string The ip address to bind to
	*/
	public final function __construct_1($address)
	{
		$this->__construct_2($address, self::PORT_DEFAULT);
	}

	/**
	* Creates an instance of the webserver
	* @param string The ip address to bind to
	* @param int The port to bind to
	*/
	public final function __construct_2($address, $port)
	{
		$this->__construct_3($address, $port, self::BUFFERLENGTH_DEFAULT);
	}

	/**
	* Creates an instance of the webserver
	* @param string The ip address to bind to
	* @param int The port to bind to
	* @param int The length of the readbuffer
	*/
	public final function __construct_3($address, $port, $bufferLength)
	{
		$this->__construct_4($address, $port, $bufferLength, self::BACKLOGAMOUNT_DEFAULT);
	}

	/**
	* Creates an instance of the webserver
	* @param string The ip address to bind to
	* @param int The port to bind to
	* @param int The length of the readbuffer
	* @param int The maximum amount of simultanuous connections in the backlog
	*/
	public final function __construct_4($address, $port, $bufferLength, $backlogAmount)
	{
		if (\System\Server\SAPI::getSAPI() != \System\Server\SAPI::SAPI_CLI)
		{
			throw new \System\Error\Exception\SystemException('Please run the server in CLI mode');
		}

		$this->maximumBufferSize = $bufferLength;

		register_shutdown_function(function()
		{
			global $argv;

			$restart = 0;

			echo 'Server is terminated.';

			$options = getopt('', array('restart::'));
			if ((isset($options['restart'])) &&
				(ctype_digit($options['restart'])))
			{
				$restart = $options['restart'];
			}

			$restart--;

			if ($restart >= 0)
			{
				for($x = 1; $x < count($argv); $x++)
				{
					if (stripos($argv[$x], '--restart=') !== false)
					{
						$argv[$x] = '--restart=' . $restart;
					}
				}

				echo ' Restarting...' . PHP_EOL;
				if (function_exists('pcntl_exec'))
				{
					pcntl_exec(PHP_BINARY, $argv);
				}
				else
				{
					$command = '"' . PHP_BINARY . '" ' . implode(' ', $argv);
					shell_exec($command . ' &');
				}
			}
		});

		//create the socket over ipv4 over tcp
		$this->masterSocket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->handleSocketError();

		//we allow the socket to be reused
		@socket_set_option($this->masterSocket, SOL_SOCKET, SO_REUSEADDR, 1) or $this->handleSocketError();

		//bind the socket to the given address
		@socket_bind($this->masterSocket, $address, $port) or $this->handleSocketError();

		//the the backlog amount for concurrent connections
		@socket_listen($this->masterSocket, $backlogAmount) or $this->handleSocketError();

		$this->sockets[self::MASTERSOCKET_KEY] = $this->masterSocket;
	}

	/**
	* This function can be overridden to only allow specific hosts.
	* Example: for when you only want to accept hosts from our-domain.com, but want to reject
	* your-domain.com
	* @param string The hostname
	* @return bool True if an allowed host, false otherwise
	*/
	protected function hostAllowed($hostName)
	{
		return true;
	}

	/**
	* This function can be overridden to only allow specific origins.
	* Example: when the origin is not what is expected, you can return false.
	* @param string The origin
	* @return bool True if an allowed origin, false otherwise
	*/
	protected function originAllowed($origin)
	{
		return true;
	}

	/**
	* This function can be overridden to only allow specific protocols
	* @param string The protocol given
	* @return bool True if the protocol is allowed, false otherwise
	*/
	protected function websocketProtocolAllowed($protocol)
	{
		return true;
	}

	/**
	* This function can be overridden to check for specific websocket extensions
	* @param string The extensions
	* @return bool True if the extensions are allowed, false otherwise
	*/
	protected function websocketExtensionsAllowed($extensions)
	{
		return true;
	}

	/**
	* This function can be overridden to output specific protocols.
	* Must return either "Sec-WebSocket-Protocol: SelectedProtocolFromClientList\r\n" or return an empty string.
	* The carriage return/newline combo must appear at the end of a non-empty string, and must not
    * appear at the beginning of the string nor in an otherwise empty string, or it will be considered part of
    * the response body, which will trigger an error in the client as it will not be formatted correctly.
	* @param string The protocol
	* @return string The string to send to the client
	*/
	protected function processProtocol($protocol)
	{
		return '';
	}

	/**
	* This function can be overridden to output specific extensions
	* Must return either "Sec-WebSocket-Extensions: SelectedExtensions\r\n" or return an empty string.
	* The carriage return/newline combo must appear at the end of a non-empty string, and must not
    * appear at the beginning of the string nor in an otherwise empty string, or it will be considered part of
    * the response body, which will trigger an error in the client as it will not be formatted correctly.
	* @param string The extensions
	* @return string The string to send to the client
	*/
	protected function processExtensions($extensions)
	{
		return '';
	}

	/**
	* Handles the socket errors and throws an exception with error information.
	* When the throwException parameter is false, it wont throw an exception, but directly outputs the
	* error message to the outbutbuffer.
	* @param bool True to throw an exception, false to just ignore the error and continue.
	* @return bool Always true
	*/
	private function handleSocketError($throwException = true)
	{
		$errorCode = socket_last_error();
		$errorMessage = socket_strerror($errorCode);

		$message = "Could not initialize socket: [$errorCode] $errorMessage";

		if ($throwException)
		{
			throw new \System\Error\Exception\SystemException($message);
		}

		//we output the message directly to the outputbuffer
		echo $message . PHP_EOL;

		return true;
	}

	/**
	* The main processing loop. This function is not supposed to exit.
	*/
	public final function run()
	{
		$time = time();

		while (true)
		{
			//we make sure the mastersocket is in the sockets list
			if (empty($this->sockets))
			{
				$this->sockets[self::MASTERSOCKET_KEY] = $this->masterSocket;
			}

			$read = $this->sockets; //these sockets are wachted for reading
			$write = array(); //these sockets are watched for writing
			$except = array(); //these sockets are watched for exceptions

			//we check for the changes in the socket list, and this modifies the arrays
			if (@socket_select($read, $write, $except, 0) === false) //nonblocking
			{
				$this->handleSocketError();
			}

			//we iterate over all the read sockets to process them
			foreach ($read as $socket)
			{
				//when the socket is the master, we check if we can accept a new connection
				if ($socket == $this->masterSocket)
				{
					if ($client = @socket_accept($socket)) //assignment
					{
						$this->connect($client);
					}
					else
					{
						$this->handleSocketError(false);
					}
				}
				else
				{
					//there is a client socket with info to read
					$buffer = null;
					$numberOfBytes = @socket_recv($socket, $buffer, $this->maximumBufferSize, 0);
					if ($numberOfBytes === false)
					{
						$this->handleSocketError(false);
						$this->disconnect($socket);
					}
					elseif ($numberOfBytes == 0)
					{
						$this->disconnect($socket);
					}
					else
					{
						$connection = $this->getConnectionBySocket($socket);
						if (!$connection->getHandshake())
						{
							$tmp = str_replace("\r", '', $buffer);
							if (strpos($tmp, "\n\n") === false)
							{
								//the client has not finished sending the initiation header, so we wait another cycle
								continue;
							}
							$this->doHandshake($connection, $buffer);
						}
						else
						{
							//we split the packet into frame and send it to deframe
							$this->splitPacket($numberOfBytes, $buffer, $connection);
						}
					}
				}
			}

			//prepare a cycle
			$connections = new \System\Collection\Vector();
			foreach ($this->connections as $connection)
			{
				if (($connection != $this->masterSocket) &&
					(!$connection->isHandlingPartialPacket()) &&
					(!$connection->isSendingContinuous()) &&
					(!$connection->isSentClose()))
				{
					$connections->add($connection);
				}
			}

			$onCycleEvent = new \System\Web\Websocket\Event\OnCycleEvent();
			$onCycleEvent->setServer($this);
			$onCycleEvent->setConnections($connections);
			$onCycleEvent->raise();

			if ($time != time())
			{
				$on1SCycleEvent = new \System\Web\Websocket\Event\On1SCycleEvent();
				$on1SCycleEvent->setServer($this);
				$on1SCycleEvent->setConnections($connections);
				$on1SCycleEvent->raise();
				$time = time();
			}
		}
	}

	/**
	* Sends a message to the given connection
	* @param Connection The connection to send a well-formed message to
	* @param string The well-formed message to send
	*/
	public final function send(\System\Web\Websocket\Connection $connection, $message)
	{
		$message = $this->frame($message, $connection);
		$result = @socket_write($connection->getSocket(), $message, strlen($message));
		if ($result === false)
		{
			$this->handleSocketError(false);
			$this->disconnect($connection->getSocket());
		}
	}

	/**
	* Extracts the actual message from the entire message)
	* @param string The message
	* @param array The header
	* @return string The message payload
	*/
	private function extractPayload($message, array $header)
	{
		return substr($message, $this->calculateOffset($header));
	}

	/**
	* Calculates the offset of the header
	* @param array The header
	* @return int The offset
	*/
	private function calculateOffset(array $header)
	{
		$offset = 2;
		if ($header[HeaderField::FIELD_HASMASK])
		{
			$offset += 4;
		}
		if ($header[HeaderField::FIELD_LENGTH] > \System\Math\Math::INT16_UNSIGNED)
		{
			$offset += 8;
		}
		elseif ($header[HeaderField::FIELD_LENGTH] > 125)
		{
			$offset += 2;
		}
		return $offset;
	}

	/**
	* Split the packet in multiple frames if needed.
	* @param int The length
	* @param string The packet
	* @param Connection The connection used
	*/
	private function splitPacket($length, $packet, \System\Web\Websocket\Connection $connection)
	{
		//we check if we have a partial packet and if so, we append the already recieved data and update the length
		if ($connection->isHandlingPartialPacket())
		{
			$packet = $connection->getPartialBuffer() . $packet;
			$length = strlen($packet);
		}

		$fullPacket = $packet;
		$framePosition = 0;
		$frameId = 1;

		while ($framePosition < $length)
		{
			$header = $this->extractHeader($packet);
			$headerSize = $this->calculateOffset($header);
			$frameSize = $header[HeaderField::FIELD_LENGTH] + $headerSize;

			//split the frame from the packet and process it
			$frame = substr($fullPacket, $framePosition, $frameSize);

			if (($message = $this->deframe($frame, $connection, $header)) !== false)
			{
				if ($connection->isSentClose())
				{
					$this->disconnect($connection->getSocket());
				}
				else
				{
					if (mb_check_encoding($message, 'UTF-8'))
					{
						$onProcessEvent = new \System\Web\Websocket\Event\OnProcessEvent();
						$onProcessEvent->setServer($this);
						$onProcessEvent->setConnection($connection);
						$onProcessEvent->setMessage($message);
						$onProcessEvent->raise();
					}
					else
					{
						//invalid encoding, perhaps need some more handling
					}
				}
			}

			$framePosition += $frameSize;
			$packet = substr($fullPacket, $framePosition);
			$frameId++;
		}
	}

	/**
	* Takes the message and processes its information to return it to normal readable content
	* @param string The message
	* @param Connection The connection
	* @return The decoded payload, or false
	*/
	private function deframe($message, \System\Web\Websocket\Connection $connection)
	{
		$header = $this->extractHeader($message);

		$pongReply = false;
		$willClose = false;

		switch ($header[HeaderField::FIELD_OPCODE])
		{
			case MessageType::TYPE_CONTINUOUS:
			case MessageType::TYPE_TEXT:
			case MessageType::TYPE_BINARY:
			case MessageType::TYPE_PONG:
				break;
			case MessageType::TYPE_CLOSE:
				$connection->setSentClose(true);
				return '';
			case MessageType::TYPE_PING:
				$pongReply = true;
				break;
			default:
				$willClose = true;
				break;
		}

		if ($this->checkRSVBits($header, $connection))
		{
			return false;
		}

		if ($willClose)
		{
			//$this->disconnect($connection);
			return false;
		}

		$payload = $connection->getPartialMessage() . $this->extractPayload($message, $header);

		if ($pongReply)
		{
			$reply = $this->frame($payload, $connection, MessageType::TYPE_PONG);
			if (@socket_write($connection->getSocket(), $reply, strlen($reply)) === false)
			{
				$this->handleSocketError(false);
				$this->disconnect($connection->getSocket());
			}
			return false;
		}

		if ($header[HeaderField::FIELD_LENGTH] > mb_strlen($this->applyMask($header, $payload)))
		{
			$connection->setHandlingPartialPacket(true);
			$connection->setPartialBuffer($message);
			return false;
		}

		$payload = $this->applyMask($header, $payload);
		if ($header[HeaderField::FIELD_FIN])
		{
			$connection->setPartialMessage('');
			return $payload;
		}

		$connection->setPartialMessage($payload);
		return false;
	}

	/**
	* Performs the handshake between the client and the server. Also checks if
	* the client is allowed to connect based on its credentials and properties
	* @param \System\Web\Websocket\Connection The connection to initiate
	* @param string The buffer
	*/
	private function doHandshake(\System\Web\Websocket\Connection $connection, $buffer)
	{
		$requestHeaders = array();

		$lines = explode("\n", $buffer);
		foreach ($lines as $line)
		{
			//the response to send to the client
			$response = '';

			if (strpos($line, ':') !== false)
			{
				$headerLine = explode(':', $line, 2);
				$requestHeaders[strtolower(trim($headerLine[0]))] = trim($headerLine[1]);
			}
			elseif (stripos($line, 'get') !== false)
			{
				preg_match("/GET (.*) HTTP/i", $buffer, $requestResource);
				$requestHeaders['get'] = trim($requestResource[1]);
			}
		}

		if (isset($requestHeaders['get']))
		{
			$connection->setRequestedResource($requestHeaders['get']);
		}
		else
		{
			$response = self::HTTP_METHOD_NOT_ALLOWED;
		}

		if ((!isset($requestHeaders['host'])) ||
			(!$this->hostAllowed($requestHeaders['host'])))
		{
			$response = self::HTTP_BAD_REQUEST;
		}
		if ((!isset($requestHeaders['upgrade'])) ||
			(strtolower($requestHeaders['upgrade']) != 'websocket'))
		{
      		$response = self::HTTP_BAD_REQUEST;
    	}
    	if ((!isset($requestHeaders['connection'])) ||
    		(strpos(strtolower($requestHeaders['connection']), 'upgrade') === false))
    	{
      		$response = self::HTTP_BAD_REQUEST;
    	}
    	if (!isset($requestHeaders['sec-websocket-key']))
    	{
      		$response = self::HTTP_BAD_REQUEST;
    	}

    	if ((!isset($requestHeaders['sec-websocket-version'])) ||
    		(strtolower($requestHeaders['sec-websocket-version']) != 13))
    	{
      		$response = self::HTTP_UPGRADE_REQUIRED;
    	}
    	if ((($this->getHeaderOriginRequired()) && (!isset($requestHeaders['origin']))) ||
    		(($this->getHeaderOriginRequired()) && (!$this->originAllowed($requestHeaders['origin']))))
    	{
      		$response = self::HTTP_FORBIDDEN;
    	}

    	if ((($this->getHeaderSecWebSocketProtocolRequired()) && (!isset($requestHeaders['sec-websocket-protocol']))) ||
    		(($this->getHeaderSecWebSocketProtocolRequired()) && (!$this->websocketProtocolAllowed($requestHeaders['sec-websocket-protocol']))))
    	{
      		$response = "HTTP/1.1 400 Bad Request";
    	}
    	if ((($this->getHeaderSecWebSocketExtensionsRequired()) && (!isset($requestHeaders['sec-websocket-extensions']))) ||
    		(($this->getHeaderSecWebSocketExtensionsRequired()) && (!$this->websocketExtensionsAllowed($requestHeaders['sec-websocket-extensions']))))
    	{
      		$response = "HTTP/1.1 400 Bad Request";
    	}

    	if (!empty($response))
    	{
    		if (@socket_write($connection->getSocket(), $response, strlen($response)) === false)
    		{
    			$this->handleSocketError(false);
			}
    		$this->disconnect($connection->getSocket());
    		return;
		}

		$connection->setHeaders($requestHeaders);
		$connection->setHandshake($buffer);

		$websocketKeyHash = sha1($requestHeaders['sec-websocket-key'] . self::WEBSOCKET_GUID);
		$rawToken = '';
		for ($i = 0; $i < 20; $i++)
		{
			$rawToken .= chr(hexdec(substr($websocketKeyHash, $i * 2, 2)));
		}
		$handshakeToken = base64_encode($rawToken) . "\r\n";

		$subProtocol = (isset($requestHeaders['sec-websocket-protocol'])) ? $this->processProtocol($requestHeaders['sec-websocket-protocol']) : "";
    	$extensions = (isset($requestHeaders['sec-websocket-extensions'])) ? $this->processExtensions($requestHeaders['sec-websocket-extensions']) : "";

		$handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $handshakeToken$subProtocol$extensions\r\n";
		if (@socket_write($connection->getSocket(), $handshakeResponse, strlen($handshakeResponse)) === false)
		{
			$this->handleSocketError(false);
			$this->disconnect($connection->getSocket());
		}
		else
		{
			$onConnectedEvent = new \System\Web\Websocket\Event\OnConnectedEvent();
			$onConnectedEvent->setServer($this);
			$onConnectedEvent->setConnection($connection);
			$onConnectedEvent->raise();
		}
	}

	/**
	* Disconnect the socket when there is nothing more to do
	* @param resource The socket for the closing client
	* @param bool True to trigger the closed function
	*/
	private function disconnect($clientSocket, $triggerClosed = true)
	{
		$disconnectedConnection = $this->getConnectionBySocket($clientSocket);

		if ($disconnectedConnection)
		{
			if (array_key_exists($disconnectedConnection->getId(), $this->connections))
			{
				unset($this->connections[$disconnectedConnection->getId()]);
			}

			if (array_key_exists($disconnectedConnection->getId(), $this->sockets))
			{
				unset($this->sockets[$disconnectedConnection->getId()]);
			}

			if ($triggerClosed)
			{
				@socket_close($disconnectedConnection->getSocket);

				//we throw a new event to handle closed connections
				$onClosedEvent = new \System\Web\Websocket\Event\OnClosedEvent();
				$onClosedEvent->setServer($this);
				$onClosedEvent->setConnection($disconnectedConnection);
				$onClosedEvent->raise();
			}
			else
			{
				$message = $this->frame('', $disconnectedConnection, \System\Web\Websocket\MessageType::TYPE_CLOSE);
				if (@socket_write($disconnectedConnection->getSocket(), $message, strlen($message)) === false)
				{
					$this->handleSocketError(false);
				}
			}
		}
	}

	/**
	* Closes a connection.
	* @param Connection The connection to close
	*/
	public function close(\System\Web\Websocket\Connection $connection)
	{
		$message = $this->frame('', $connection, \System\Web\Websocket\MessageType::TYPE_CLOSE);
		if (@socket_write($connection->getSocket(), $message, strlen($message)) === false)
		{
			$this->handleSocketError(false);
		}
		$this->disconnect($connection->getSocket());
	}

	/**
	* Returns the connection object corresponding to the given socket
	* @param resource The client socket to search for
	* @return \System\Web\Websocket\Connection The connection object, or null on not found
	*/
	private function getConnectionBySocket($clientSocket)
	{
		foreach ($this->connections as $connection)
		{
			if ($connection->getSocket() == $clientSocket)
			{
				return $connection;
			}
		}

		return null;
	}

	/**
	* Registers the socket with a new Connection object and stores that
	* @param resource The socket for the new client
	*/
	private function connect($clientSocket)
	{
		$connection = new Connection($clientSocket);
		$this->connections[$connection->getId()] = $connection;
		$this->sockets[$connection->getId()] = $clientSocket;

		//we throw a new event to handle new connections
		$onConnectingEvent = new \System\Web\Websocket\Event\OnConnectingEvent();
		$onConnectingEvent->setServer($this);
		$onConnectingEvent->setConnection($connection);
		$onConnectingEvent->raise();
	}

	/**
	* Checks the RSV bits, used for extensions
	* @param array The header
	* @param Connection The connection
	* @return bool True if valid
	*/
	private function checkRSVBits(array $header, \System\Web\Websocket\Connection $connection)
	{
		return ((ord($header[HeaderField::FIELD_RSV1]) +
				ord($header[HeaderField::FIELD_RSV2]) +
				ord($header[HeaderField::FIELD_RSV3])) > 0);
	}

	/**
	* Applies the mask to the payload
	* @param array The header
	* @param string The payload
	* @return string The masked payload
	*/
	private function applyMask(array $header, $payload)
	{
		$effectiveMask = '';
		if ($header[HeaderField::FIELD_HASMASK])
		{
			$mask = $header[HeaderField::FIELD_MASK];
		}
		else
		{
			return $payload;
		}

		while (strlen($effectiveMask) < strlen($payload))
		{
			$effectiveMask .= $mask;
		}

		while (strlen($effectiveMask) > strlen($payload))
		{
			$effectiveMask = substr($effectiveMask, 0, -1);
		}

		return $effectiveMask ^ $payload;
	}

	/**
	* Extracts the header from the given message
	* @param string The message buffer
	* @return array An array with the header
	*/
	private function extractHeader($message)
	{
		$header = array(
			HeaderField::FIELD_FIN		=> $message[0] & chr(128),
			HeaderField::FIELD_RSV1		=> $message[0] & chr(64),
			HeaderField::FIELD_RSV2		=> $message[0] & chr(32),
			HeaderField::FIELD_RSV3		=> $message[0] & chr(16),
			HeaderField::FIELD_OPCODE	=> ord($message[0]) & 15,
			HeaderField::FIELD_HASMASK	=> $message[1] & chr(128),
			HeaderField::FIELD_LENGTH	=> 0,
			HeaderField::FIELD_MASK		=> ''
		);

		$header[HeaderField::FIELD_LENGTH] = (ord($message[1]) >= 128) ? ord($message[1]) - 128 : ord($message[1]);

		if ($header[HeaderField::FIELD_LENGTH] == 126)
		{
			if ($header[HeaderField::FIELD_HASMASK])
			{
				$header[HeaderField::FIELD_MASK] = $message[4] . $message[5] . $message[6] . $message[7];
			}

			$header[HeaderField::FIELD_LENGTH] = (ord($message[2]) * 256) + ord($message[3]);
		}
		elseif ($header[HeaderField::FIELD_LENGTH] == 127)
		{
			if ($header[HeaderField::FIELD_HASMASK])
			{
        		$header[HeaderField::FIELD_MASK] = $message[10] . $message[11] . $message[12] . $message[13];
      		}

      		$header[HeaderField::FIELD_LENGTH] =
      			  ord($message[2]) * 65536 * 65536 * 65536 * 256
                + ord($message[3]) * 65536 * 65536 * 65536
                + ord($message[4]) * 65536 * 65536 * 256
                + ord($message[5]) * 65536 * 65536
                + ord($message[6]) * 65536 * 256
                + ord($message[7]) * 65536
                + ord($message[8]) * 256
                + ord($message[9]);
		}
		elseif ($header[HeaderField::FIELD_HASMASK])
		{
			$header[HeaderField::FIELD_MASK] = $message[2] . $message[3] . $message[4] . $message[5];
		}

		return $header;
	}

	/**
	* Creates a frame for the given message
	* @param string The message
	* @param \System\Web\Websocket\Connection The connection
	* @param int The messagetype, see \System\Web\Websocket\MessageType
	* @param bool True if the message continuous, false otherwise
	* @return string A frame
	*/
	private function frame($message, \System\Web\Websocket\Connection $connection, $messageType = \System\Web\Websocket\MessageType::TYPE_TEXT, $messageContinues = false)
	{
		$b1 = \System\Web\Websocket\MessageType::getMessageType($connection, $messageType);

		if ($messageContinues)
		{
			$connection->setSendingContinuous(true);
		}
		else
		{
			$b1 += 128;
			$connection->setSendingContinuous(false);
		}

		$length = strlen($message);
		$lengthField = '';
		if ($length < 126)
		{
			$b2 = $length;
		}
		elseif ($length <= 65536)
		{
			$b2 = 126;
			$hexLength = dechex($length);
			if (strlen($hexLength) % 2 == 1)
			{
				$hexlength = '0' . $hexLength;
			}
			$n = strlen($hexLength) - 2;

			for ($i = $n; $i >= 0; $i = $i - 2)
			{
				$lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
			}

			while (strlen($lengthField) < 2)
			{
				$lengthField = chr(0) . $lengthField;
			}
		}
		else
		{
			$b2 = 127;
			$hexLength = dechex($length);
			if (strlen($hexLength) % 2 == 1)
			{
        		$hexLength = '0' . $hexLength;
      		}
      		$n = strlen($hexLength) - 2;

      		for ($i = $n; $i >= 0; $i = $i - 2)
      		{
        		$lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
      		}

      		while (strlen($lengthField) < 8)
      		{
        		$lengthField = chr(0) . $lengthField;
      		}
		}

		return chr($b1) . chr($b2) . $lengthField . $message;
	}
}