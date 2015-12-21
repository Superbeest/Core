<?php
/**
* Call.class.php
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
* Contains functionality to create HTTP request calls
* @package \System\HTTP\Request
*/
class Call extends \System\Base\StaticBase
{
    /**
    * The default timeout in seconds for Asyncronous HTTP calls
    */
    const ASYNC_TIMEOUT = 5;
    /**
    * The default port for Asynchronous HTTP calls
    */
    const ASYNC_DEFAULT_PORT = 80;
	/**
	* The default timeout in seconds for synchronous HTTP calls
	*/
    const SYNC_TIMEOUT = 10;

    /**
    * Calls a page using a asynchronous connection. The call is made using a HTTP POST request.
    * After the POST request is made, the sockets are closed again.
    * @param string The url to call.
    * @param bool If true and the url cannot be reached, then an exception is thrown.
    */
    public static final function httpPageRequestAsync($url, $throwExceptionOnError = true)
    {
        $urlParts = parse_url($url);

        $errno = '';
        $errstr = '';

        $socket = @fsockopen(
                $urlParts['host'],
                isset($urlParts['port']) ? $urlParts['port'] : self::ASYNC_DEFAULT_PORT,
                $errno,
                $errstr,
                self::ASYNC_TIMEOUT);

        if ($socket)
        {
            $output = "POST " . $urlParts['path'] . " HTTP/1.1\r\n";
            $output .= "Host: " . $urlParts['host'] . "\r\n";
            $output .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $output .= "Content-Length: " . (isset($urlParts['query']) ? strlen($urlParts['query']) : 0) . "\r\n";
            $output .= "Connection: Close\r\n\r\n";

            if (isset($urlParts['query']))
            {
                $output .= $urlParts['query'];
            }

            //write to the socket and then close it again
            fwrite($socket, $output);
            fclose($socket);
        }
        else
        {
            if ($throwExceptionOnError)
            {
                throw new \System\Error\Exception\InvalidHTTPCallException('The socket could not be opened');
            }
        }
    }

	/**
	* Gets the default page headers for a standard request. This excludes user agent headers
	* These headers get applied to the httpPageRequest() when no headers are given to that function.
	* @param array Header array to be expanded, by reference
	* @return array The same header array as the parameter
	*/
    public static final function getHttpPageRequestDefaultHeaders(array &$header = array())
    {
	    $header[] = "Accept: Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
	    $header[] = "Accept-Language: nl,en-us;q=0.7,en;q=0.3";
	    $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	    $header[] = "Keep-Alive: 115";
	    $header[] = "Connection: keep-alive";
	    $header[] = "Cache-Control: max-age=0";

	    return $header;
	}

    const USERAGENT_FIREFOX_36 = 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0';
    const USERAGENT_FIREFOX_31 = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20130401 Firefox/31.0';
    const USERAGENT_FIREFOX_3_6_10 = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; nl; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10';
    const USERAGENT_CHROME_41_0_2228_0 = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
    const USERAGENT_INTERNET_EXPLORER_11 = 'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

    const REFERER_GOOGLE = 'http://www.google.com';

    /**
    * This function does a page request to a given url. The request is done using a regular HTTP GET request, and can be
    * optionally replaced by a POST request by passing in postdata.
    * This function tries to execute using the cURL library, but falls back to regular GET requests if that library isnt available.
    * If cURL is not available, then POST requests will be ignored.
    * Valid $postData parameter types are string, array, or \System\Collection\Map types. $postData is an urlencoded string like 'para1=val1&para2=val2&...'
    * or an array (or Map) with the field name as key and field data as value. If value is an array or Map, the Content-Type header will be set to multipart/form-data.
    * Support for proxies is built in. If there is no proxy url given, then no proxies will be used.
    * SSL certificates are ignored.
    * @param string The url to call.
    * @param mixed The postdata to send. If this is null, then a normal GET request is done. Not supported if cURL is not present.
    * @param string The user agent to use. Default is the Firefox 3.6 browser under a Win7 environment.
    * @param string The referer to use for redirects. Not supported if cURL is not present.
    * @param string A proxy url to connect through. This url requires a protocol (ex: http://localhost:8118)
    * @param array The return headers by reference
    * @param array Optional custom headers. If custom headers are given, no default headers will be used.
    * @param int The amount of seconds to wait before a timeout
    * @return mixed The html contents or false for an error.
    */
    public static final function httpPageRequest($url, $postData = null, $userAgent = self::USERAGENT_FIREFOX_36, $referer = self::REFERER_GOOGLE, $proxyUrl = '', &$returnHeaders = array(), array $header = array(), $timeout = self::SYNC_TIMEOUT)
    {
        $returnValue = false;

		//only set default headers if no other custom headers are given
        if (count($header) == 0)
        {
	        $header[] = "User-Agent: " . $userAgent;
	        self::getHttpPageRequestDefaultHeaders($header);
		}

        if (function_exists('curl_init'))
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_REFERER, $referer);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        	curl_setopt($curl, CURLOPT_VERBOSE, 1);

            if (!empty($proxyUrl))
            {
                curl_setopt($curl, CURLOPT_PROXY, $proxyUrl);
            }

            //there is data we want to post, so we do a POST instead of a GET
            if ($postData)
            {
                switch (true)
                {
                    case ((\System\Type::getType($postData) == \System\Type::TYPE_OBJECT) &&
                          ($postData instanceof \System\Collection\Map) &&
                          ($postData->count() > 0)):
                        $postData = $postData->getArrayCopy();
                        //fallthrough
                    case ((\System\Type::getType($postData) == \System\Type::TYPE_STRING) &&
                          (mb_strlen($postData) > 0)):
                        //fallthrough
                    case ((\System\Type::getType($postData) == \System\Type::TYPE_ARRAY) &&
                          (count($postData) > 0)):
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                        break;
                    default:
                        throw new \InvalidArgumentException('The given POST argument is not of type: string, array, \System\Collection\Map. Type given: ' . \System\Type::getType($postData));
                }
            }

            $returnValue = curl_exec($curl);

            if ($returnValue)
			{
				$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
				$returnHeaders = preg_split('/$\R?^/m', substr($returnValue, 0, $headerSize));
				$returnValue = substr($returnValue, $headerSize);
			}
            curl_close($curl);
        }
        else
        {
            //fallback to manual http calls using file_get_contents. We only support GET calls here.
            //we add support for gzip,deflate
            $header[] = "Accept-Encoding: gzip,deflate";

            $options = array(
                'http' => array(
                    'method' => 'GET',
                    'header' => implode('\r\n', $header)));

            $context = stream_context_create($options);
            $returnValue = file_get_contents($url, NULL, $context);
        }

        return $returnValue;
    }
}
