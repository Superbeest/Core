<?php
/**
* Onetime.ctr.php
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

namespace Module\Onetimecall;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}
/**
* The onetimecall controller
* @package \Module\Onetimecall
*/
final class Onetime extends \System\Web\Controller
{
    protected function deployServices(\System\Collection\Vector $services)
    {
    	$services[] = new \System\Web\ServiceEntry('\Module\Onetimecall\Service\OnetimeService::call');
    }

	/**
	* The defaultaction should never be used wo the throw an exception
	*/
    public final function defaultAction()
    {
		throw new \System\Error\Exception\MethodNotImplementedException('Default action should never be called here');
	}

	public final function call()
	{
		if ($this->hasServiceResult('\Module\Onetimecall\Service\OnetimeService::call'))
		{
			$serviceResult = $this->getServiceResult('\Module\Onetimecall\Service\OnetimeService::call');
			$callbackKey = $serviceResult->onetimeCall->getKey();

			$validCalls = $serviceResult->onetimeCall->getRegisteredCalls();

			if ($validCalls->keyExists($callbackKey))
			{
				$callback = $validCalls->$callbackKey;
				$db = $this->getDefaultDb();
				$surface = call_user_func_array($callback, array($db, $serviceResult->onetimeCall));

				$this->setRenderSurface($surface);
				return;
			}
		}

		throw new \InvalidArgumentException('The given Onetimecall is invalid');
	}
}
