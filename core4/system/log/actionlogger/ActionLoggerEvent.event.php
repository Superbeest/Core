<?php
/**
* ActionLoggerEvent.class.php
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


namespace System\Log\ActionLogger;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Logs all activity from incoming requests.
* @package \System\Log\ActionLogger
*/
class ActionLoggerEvent extends \System\Base\StaticBase
{
    /**
    * @var array This list gets excluded from the logging and can be filled to your liking
    */
    public static $excludeControllers = array();

    /**
    * @var array This list gets excluded from the logging and can be filled to your liking
    */
    public static $excludeIps = array();

    /**
    * Every call from the system goes through here, so we are able to log it.
    * @param \System\Event\Event\OnBeforeControllerLoadEvent The controller load event. Do note, the controller is not yet executed.
    */
    public static final function log(\System\Event\Event\OnBeforeControllerLoadEvent $event)
    {
        $ip = \System\HTTP\Visitor\IP::c_getClientIP();

        //return when we want to ignore this specific IP
        foreach (static::$excludeIps as $excludeIP)
        {
            if (\System\HTTP\Visitor\IP::isIPMatch($ip, $excludeIP))
            {
                return;
            }
        }

        $browserQuery = \System\HTTP\Request\Request::c_getQuery() ?: '';
        $referer = \System\HTTP\Request\Request::c_getReferer() ?: '';
        $request = \System\HTTP\Request\Request::c_getRequest() ?: '';

        //return when the request is in the exclude list
        foreach (static::$excludeControllers as $exclude)
        {
            if (mb_strpos($browserQuery, $exclude) !== false)
            {
                return;
            }
        }

        $browser = \System\HTTP\Visitor\Browser::c_getBrowser() ?: 'Unknown';
        $browserVersion = \System\HTTP\Visitor\Browser::c_getBrowserVersion() ?: '';
        $os = \System\HTTP\Visitor\Browser::c_getClientOS() ?: 'Unknown';
        $userAgent = \System\HTTP\Visitor\Browser::c_getUserAgent()  ?: 'Unknown';

        $post = new \System\HTTP\Request\Post();
        $postData = $post->serialize();
        $get = new \System\HTTP\Request\Get();
        $getData = $get->serialize();

        $reg = \System\Register\Register::getInstance();
        $db = $reg->defaultDb;

        if ($db)
        {
            $query = new \System\Db\Query($db, \System\Log\ActionLogger\SQL_ACTIONLOGGEREVENT_STORE);
            $query->bind($browserQuery, \System\Db\QueryType::TYPE_STRING);
            $query->bind($ip, \System\Db\QueryType::TYPE_STRING);
            $query->bind($referer, \System\Db\QueryType::TYPE_STRING);
            $query->bind($request, \System\Db\QueryType::TYPE_STRING);
            $query->bind($browser . ' ' . $browserVersion, \System\Db\QueryType::TYPE_STRING);
            $query->bind($userAgent, \System\Db\QueryType::TYPE_STRING);
            $query->bind($os, \System\Db\QueryType::TYPE_STRING);
            $query->bind($postData, \System\Db\QueryType::TYPE_STRING);
            $query->bind($getData, \System\Db\QueryType::TYPE_STRING);

            $db->query($query);
        }
    }

    public static final function logQuery(\System\Event\Event\OnMySQLQueryEvent $event)
    {
		$query = $event->getQuery();

		$file = new \System\IO\File(PATH_LOGS . 'query.txt', false);

		$contents = '';
		if ($file->exists())
		{
			$contents = $file->getContents();
		}

		$contents .= $query->getQuery() . "\r\n";

		\System\IO\File::writeContents(PATH_LOGS . 'query.txt', $contents);
	}

	public static final function logSlowQuery(\System\Event\Event\OnSlowMySQLQueryEvent $event)
    {
		$query = $event->getQuery();

		$file = new \System\IO\File(PATH_LOGS . 'slow_query.txt', false);

		$contents = '';
		if ($file->exists())
		{
			$contents = $file->getContents();
		}

		$contents .= $query->getQuery() . "\r\n";

		\System\IO\File::writeContents(PATH_LOGS . 'slow_query.txt', $contents);
	}
}