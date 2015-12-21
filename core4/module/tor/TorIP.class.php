<?php
/**
* TorIP.class.php
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


namespace Module\Tor;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Implements tor IP functionality
* @package \Module\Tor
*/
class TorIP extends \System\Base\StaticBase
{
	/**
	* The lookup suffix for hostname resolving
	*/
	const LOOKUPADDRESS_SUFFIX = '.80.104.161.233.64.ip-port.exitlist.torproject.org';

	/**
	* When the resolved hostname equals this host, it is a tornode
	*/
	const TORIP_HOST = '127.0.0.2';

	/**
	* Return if the given IPv4 address is a known TOR exit node.
	* @param string $ipAddress The ip address to check against
	* @return boolean True if the given ip address is a known TOR exit node, false otherwise
	*/
	public static final function isTORIP($ipAddress)
	{
		//when run local, we dont do a lookup.
		if ($ipAddress == \System\HTTP\Visitor\IP::IP_LOCALHOST)
		{
			return false;
		}

		$ipBlocks = explode('.', $ipAddress);
		$ipBlocks = array_reverse($ipBlocks);
		$ipAddress = implode('.', $ipBlocks);

		$lookupAddres = $ipAddress . self::LOOKUPADDRESS_SUFFIX;

		return (gethostbyname($lookupAddres) == self::TORIP_HOST);
	}
}