<?php
/**
* ServerBase.class.php
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


namespace System\HTTP\Request;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Contains functions for retrieval of server variables, related to the request
* @package \System\HTTP\Request
*/
class Request extends \System\HTTP\ServerBase
{
    /**
    * Returns the protocol used.
    * @return string The protocol used.
    */
    public static final function getProtocol()
    {
        $handle = self::getServerHandle();
        return $handle['SERVER_PROTOCOL'];
    }

    /**
    * Returns the method used.
    * A valid value from \System\HTTP\Request\Method must be used, or METHOD_GET will be returned
    * @return string The method used.
    */
    public static final function getMethod()
    {
        $handle = self::getServerHandle();
        $method = $handle['REQUEST_METHOD'];
        $val = new \System\Security\Validate();
        if ($val->inStruct($method, 'method', new \System\HTTP\Request\Method(), true) == \System\Security\ValidateResult::VALIDATE_OK)
        {
        	return $method;
		}
        return \System\HTTP\Request\Method::METHOD_GET;
    }

    /**
    * Returns the time of the request
    * @return string The time of the request
    */
    public static final function getTime()
    {
        $handle = self::getServerHandle();
        return $handle['REQUEST_TIME'];
    }

    /**
    * Returns the query of the request
    * @return string The query of the request
    */
    public static final function getQuery()
    {
        $handle = self::getServerHandle();
        return $handle['QUERY_STRING'];
    }

    /**
    * Returns the connection string
    * @return string The connection string
    */
    public static final function getConnection()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_CONNECTION'];
    }

    /**
    * Returns the referer string
    * @return string The referer string
    */
    public static final function getReferer()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_REFERER'];
    }

    /**
    * Returns the host
    * @return string The host
    */
    public static final function getHost()
    {
        $handle = self::getServerHandle();
        return $handle['HTTP_HOST'];
    }

    /**
    * Returns the host
    * @return string The host
    */
    public static final function getClientHost()
    {
        $handle = self::getServerHandle();
        return $handle['REMOTE_HOST'];
    }

    /**
    * Returns the port used for communication, clientside
    * @return string The port
    */
    public static final function getClientPort()
    {
        $handle = self::getServerHandle();
        return $handle['REMOTE_PORT'];
    }

    /**
    * Returns the port used for communication, serverside
    * @return string The port
    */
    public static final function getPort()
    {
        $handle = self::getServerHandle();
        return $handle['SERVER_PORT'];
    }

	/**
	* Returns the IP address of the server.
	* @return string The server IP address
	*/
    public static final function getServerAddress()
    {
    	$handle = self::getServerHandle();
    	return $handle['SERVER_ADDR'];
	}

    /**
    * Returns the request send to the webserver
    * @return string The request to the server
    */
    public static final function getRequest()
    {
        $handle = self::getServerHandle();
        return $handle['REQUEST_URI'];
    }

    /**
    * This function returns the current URL without the query string, fragments, and username/passwords.
    * Also, port definitions are not accessed.
    * @return string The ident url
    */
    public static final function getIdentUrl()
    {
        $handle = self::getServerHandle();

        $pageUrl = 'http';
        if ((isset($handle['HTTPS'])) &&
            ($handle['HTTPS'] != 'off'))
        {
            $pageUrl .= 's';
        }

        $pageUrl .= '://';

        $pageUrl .= self::getHost();

        if (self::getPort() != '80')
        {
            $pageUrl .= ':' . self::getPort();
        }

        $pageUrl .= self::getRequest();

        $urlParts = parse_url($pageUrl);

        $ident = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];

        if (mb_substr($ident, -1) != '/')
        {
            $ident .= '/';
        }

        return $ident;
    }
}