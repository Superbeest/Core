<?php
/**
* FileResponse.class.php
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
* Implements the reply to the message
* @package \System\System\Interaction
*/
class FileResponse extends Response
{
	/**
	* Constructs the object
	* @param \System\System\Interaction\Message The original message
	* @param \System\IO\File A file object, or the data to send
	*/
	public function __construct(\System\System\Interaction\Message $originalMessage, $value)
	{
		if ($value instanceof \System\IO\File)
		{
			$value = @base64_encode($value->getContents());
		}

		parent::__construct($originalMessage, $value);
	}

	/**
	* Returns the file data
	* @return string The filedata or fale if decoding failed
	*/
	public final function getFileData()
	{
		return @base64_decode($this->getValue());
	}
}