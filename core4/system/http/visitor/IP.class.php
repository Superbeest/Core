<?php
/**
* IP.class.php
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


namespace System\HTTP\Visitor;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Class that provides functionality to identify the current user using its ip address.
* It is based on the \System\HTTP\Request\Server class and therefor reflects any changes
* that are made to it.
* @package \System\HTTP\Visitor
*/
class IP extends \System\HTTP\ServerBase
{
    /**
    * Invalid IP address
    */
    const IP_INVALID = '0.0.0.0';

	/**
	* Localhost
	*/
    const IP_LOCALHOST = '127.0.0.1';

    /**
    * Returns the IP of the current client.
    * The IP address is validated against the security validator. In case of an invalid IP, IP_INVALID is returned.
    * @param bool Get the shorthand notation of an address.
    * @return string The ip address of the client.
    */
    public static final function getClientIP($shortHand = true)
    {
        //validate the handle to the server variable
        $handle = self::getServerHandle();

        $ip = self::IP_INVALID;

        switch (true)
        {
            case (isset($handle['HTTP_CLIENT_IP'])):
                //if there is a HTTP_CLIENT_IP variable, we parse this and use the first value in it.
                //this allows us to properly handle loadbalancers and cachers.
			    $ar = explode(',', $handle['HTTP_CLIENT_IP']);
			    $ip = trim(array_shift($ar));
                break;
            case (isset($handle['HTTP_X_FORWARDED_FOR'])):
		        //if there is a HTTP_X_FORWARDED_FOR variable, we parse this and use the first value in it.
		        //this allows us to properly handle loadbalancers and cachers.
                $ar = explode(',', $handle['HTTP_X_FORWARDED_FOR']);
                $ip = trim(array_shift($ar));
                break;
            case (isset($handle['REMOTE_ADDR'])):
		        //if there is a REMOTE_ADDR variable, we parse this and use the first value in it.
                $ar = explode(',', $handle['REMOTE_ADDR']);
                $ip = trim(array_shift($ar));
                break;
		}

        $val = new \System\Security\Validate();
        if ($val->isIPAddress($ip, 'ip', true, true, false, false, true) == \System\Security\ValidateResult::VALIDATE_OK)
        {
            if ($shortHand)
            {
                return inet_ntop(inet_pton($ip));
            }

            return $ip;
        }

        return self::IP_INVALID;
    }

    /**
    * Checks if a given IP (only supports IPv4) matches a given IPMask. The IP mask can be a regular IPv4 address, but also allows
    * for wildcards, making 192.168.0.* match every address in the range 192.168.0.0 - 192.168.0.255.
    * Do note: this function does not check for IP validity, nor for validity in the mask.
    * @param string The ip address to check against the mask
    * @param string The ip mask.
    * @return bool True on a match, false otherwise
    */
    public static final function isIPMatch($ip, $ipMask)
    {
        $ipMask = str_replace('*', '[0-9]{1,3}', $ipMask);
        $ipMask = str_replace('.', '\.', $ipMask);
        $ipMask = '/^' . $ipMask . '$/';

        return (preg_match($ipMask, $ip) > 0);
    }

    /**
    * Converts the given IPv4 address to an unique float number.
    * Converted IP addresses are faster to search through.
    * @param string The IPv4 address as a string
    * @return float The unique integer value of the IP.
    */
    public static final function convertIPToNum($ip)
    {
        list($ip1, $ip2, $ip3, $ip4) = preg_split('/\./', $ip);

        return (16777216 * $ip1) + (65536 * $ip2) + (256 * $ip3) + $ip4;
    }

    /**
    * Converts the given unique float number to an IPv4 address.
    * @param float The unique float to convert to an IPv4 address.
    * @return string The converted IP address.
    */
    public static final function convertNumToIP($num)
    {
		if (is_float($num))
		{
			return long2ip(sprintf("%d", $num));
		}

		return long2ip($num);
    }

    /**
    * Returns whether or not IPv6 support was enabled in this PHP build. This must be done using a module, or build time.
    * @return bool true on enabled, false otherwise
    */
    public static final function hasPHPIPv6Support()
    {
        return defined('AF_INET6');
    }

	/**
	* Gets the internet host name corresponding to a given IP address. This may pose a delay as it does a remoteserver request.
	* On the default IP address, the current client IP is reversed.
	* @param string The ip (V4) address to lookup.
	* @return string The hostname on success, the original ip on failure, or false on invalid input
	*/
    public static final function getHostName($ipAddress = self::IP_INVALID)
    {
    	if ($ipAddress == self::IP_INVALID)
    	{
			$ipAddress = self::getClientIP();
		}

		$val = new \System\Security\Validate();
		if (($ipAddress == self::IP_INVALID) ||
			($val->isIPAddress($ipAddress, 'ip', true, true, false, true, false) != \System\Security\ValidateResult::VALIDATE_OK))
		{
			return self::IP_INVALID;
		}

		return gethostbyaddr($ipAddress);
	}

	/**
	* Returns true if the given IP is a GoogleBot IP
	* @param string The ip (V4) address to lookup.
	* @return bool True if the given ip belongs to googlebot, false otherwise
	*/
	public static final function isIPGoogleBot($ipAddress = self::IP_INVALID)
	{
		$hostname = self::getHostName($ipAddress);

		return mb_strripos($hostname, 'googlebot.com') !== false;
	}

	/**
	* Returns true if the given IP is a BingBot IP
	* @param string The ip (V4) address to lookup.
	* @return bool True if the given ip belongs to bingbot, false otherwise
	*/
	public static final function isIPBingBot($ipAddress = self::IP_INVALID)
	{
		$hostname = self::getHostName($ipAddress);

		return mb_strripos($hostname, 'search.msn.com') !== false;
	}
}