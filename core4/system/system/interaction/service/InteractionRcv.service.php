<?php
/**
* InteractionRcv.service.php
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


namespace System\System\Interaction\Service;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Implements the service for the Interaction system
* @package \System\System\Interaction\Service
*/
class InteractionRcv extends \System\Web\Service
{
	/**
	* Processes the interaction service request. All the specific requests will be processed and optional responses will be returned
	* @param \System\Collection\Map The serviceResult to output
	* @param \System\Db\Database The database to work with.
	*/
	public static final function interact(\System\Collection\Map $serviceResult, \System\Db\Database $defaultDb)
	{
		self::validateHandles();

		$val = self::$post->request;
		if (!empty($val))
		{
			//decodes the given content in the request post var
			$decoded = \System\System\Interaction\Interaction::decode($val);

			if ($decoded)
			{
				$reply = new \System\Collection\Vector();

				foreach ($decoded as $message)
				{
					$event = new \System\System\Interaction\Event\OnInteractionEvent();
					$event->setMessage($message);
					$event->setDatabase($defaultDb);
					$event->raise();

					$event->addResponse(new \System\System\Interaction\Response($message, 'OK'));

					foreach ($event->getResponses() as $response)
					{
						$reply[] = $response;
					}
				}

				$serviceResult->reply = $reply;
				return true;
			}
		}

		return false;
	}
}