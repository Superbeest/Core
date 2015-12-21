<?php
/**
* ErrorHandler.class.php
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

namespace System\Error;

if (!defined('System'))
{
    die ('Hacking attempt');
}

define ('DEFAULT_ERRORLEVEL', E_ALL | E_STRICT);

/**
* This class contains functions for the handling of errors and exception.
* Upon creation of this object, it registers its static handlers with PHP and makes
* sure we can catch all non fatal errors.
* @package \System\Error
*/
class ErrorHandler extends \System\Base\SingletonBase
{
    /**
    * This is the default errorpage in the system.
    */
    const ERRORPAGE = '
            <html>
            <head>
                <title>An error occured</title>
                <style type="text/css">
                    html, body {width: 100%; text-align: center; font-size: 11px; font-family: Verdana;}
                    div { padding: 10px; text-align: left; border: 1px solid black; }
                </style>
            </head>
        <body>
            <div style="width: 640px; margin: 0 auto;">
        <img style="margin-right: 10px; margin-bottom: 10px;" align="left" src="data:image/png;base64,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" />
                <h4>An error has occured.</h4>
                <p>Please see the details below. This error has been logged, so we will look into it. Your IP has been logged.</p>
                <div style="clear: both;">{ERROR}</div>
            </div>
        </body>
        </html>';

    /**
    * Overrides the parent private constructor
    */
    protected final function __construct()
    {
        register_shutdown_function('\System\Error\ErrorHandler::handleShutdown');

        //we set our own custom exception handler
        set_exception_handler('\System\Error\ErrorHandler::handleException');

        //we set our own custom error handler, with the strongest level of error checking.
        //we also explicitly include E_STRICT
        $level = defined('SYSTEM_ERRORLEVEL') ? constant('SYSTEM_ERRORLEVEL') : DEFAULT_ERRORLEVEL;
        error_reporting($level);
        //we disable the showing of errors
        if (!defined('DEBUG'))
        {
            ini_set('display_errors', false);
        }
        else
        {
            ini_set('display_errors', true);
        }
        set_error_handler('\System\Error\ErrorHandler::handleError', $level);

        //we register our own assertion handler, so we can store our failed assertions
        assert_options(ASSERT_ACTIVE, 1);
        assert_options(ASSERT_WARNING, 1);
        //we want to terminate after a failed assertion
        assert_options(ASSERT_BAIL, 1);
        assert_options(ASSERT_QUIET_EVAL, 0);
        assert_options(ASSERT_CALLBACK, '\System\Error\ErrorHandler::handleAssert');
    }

    /**
    * Returns the converted error number to a string
    * @param integer The errorcode of the error
    * @return string The textual representation of the errorcode
    */
    public static final function translateErrorNumber($errorNumber)
    {
        $levelNames = array(
            E_ERROR => 'E_ERROR',                   E_WARNING => 'E_WARNING',                   E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',                 E_CORE_ERROR => 'E_CORE_ERROR',             E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',   E_COMPILE_WARNING => 'E_COMPILE_WARNING',   E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',     E_USER_NOTICE => 'E_USER_NOTICE',			E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR', E_DEPRECATED => 'E_DEPRECATED',		E_USER_DEPRECATED => 'E_USER_DEPRECATED');

        $levels = array();
        if (($errorNumber & E_ALL) == E_ALL)
        {
            $levels[] = 'E_ALL';
            $errorNumber &= ~E_ALL;
        }

        foreach ($levelNames as $level=>$name)
        {
            if (($errorNumber & $level) == $level)
            {
                $levels[] = $name;
            }
        }

        return implode(' | ', $levels);
    }

    /**
    * Outputs the error and logs it to a file.
    * Note that this function effectively halts the execution of our script.
    * @param int The errorlevel of the error.
    * @param string The message
    * @param string The file the error occured in
    * @param int The line number of the error
    * @param string The stacktrace as a string
    */
    protected static final function outputError($errorLevel, $errorNotice, $errorFile, $errorLine, $stackTrace)
    {
        //logging of the error goes here. This is done using classic SQL queries and manual connecting to the db,
        //because we dont want to be dependant of the system

        $errorPage = self::ERRORPAGE;

        if ((defined('DATABASE_HOST')) &&
            (defined('DATABASE_USER')) &&
            (defined('DATABASE_PASS')) &&
            (defined('DATABASE_PORT')) &&
            (defined('DATABASE_NAME')))
        {
            $link = @mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME, DATABASE_PORT);
            if($link)
            {
	            $ip = @mysqli_real_escape_string($link, \System\HTTP\Visitor\IP::getClientIP());
	            $serverIp = @mysqli_real_escape_string($link, \System\HTTP\Request\Request::getServerAddress());
	            $query = @mysqli_real_escape_string($link, \System\HTTP\Request\Request::getQuery());
	            $referer = @mysqli_real_escape_string($link, \System\HTTP\Request\Request::getReferer());
	            $request = @mysqli_real_escape_string($link, \System\HTTP\Request\Request::getRequest());

	            $errorNumber = @mysqli_real_escape_string($link, $errorLevel);
	            $errorString = @mysqli_real_escape_string($link, $errorNotice);
	            $escapedError = @mysqli_real_escape_string($link, $errorFile);
	            $errorLine = @mysqli_real_escape_string($link, $errorLine);

	            $escapedTrace = @mysqli_real_escape_string($link, $stackTrace);

	            $post = new \System\HTTP\Request\Post();
		        $postData = $post->serialize();
		        $get = new \System\HTTP\Request\Get();
		        $getData = $get->serialize();

				@mysqli_query($link, "INSERT INTO syserror (syserror_code, syserror_string, syserror_file, syserror_line, syserror_timestamp, syserror_server_ip, syserror_ip, syserror_query, syserror_referer, syserror_request, syserror_stacktrace, syserror_post, syserror_get)
	                VALUES ('$errorNumber', '$errorString', '$escapedError', '$errorLine', NOW(), '$serverIp', '$ip', '$query', '$referer', '$request', '$escapedTrace', '$postData', '$getData')");

	            @mysqli_close($link);

				//remove extended notices for cli mode
	            if (\System\Server\SAPI::getSAPI() == \System\Server\SAPI::SAPI_CLI)
	            {
	            	$errorPage = '{ERROR}';
				}

	            if (!defined('DEBUG'))
		        {
		            $errorPage = str_replace('{ERROR}', "<p><b>Details:</b></p><p>The page you requested could not be found. Please contact the webmaster.</p>", $errorPage);
		        }
		        else
		        {
		            $errorPage = str_ireplace("{ERROR}", "<p><b>Details:</b></p><p><b>Errorcode</b>: " . $errorNumber . " - " . self::translateErrorNumber($errorNumber) .
		                "</p><p><b>Error message</b>: " . $errorNotice .
		                "</p><p><b>Errorneous file</b>: " . $errorFile .
		                "</p><p><b>On line</b>: " . $errorLine .
		                "</p><p><b>Stacktrace</b>:<br />" . nl2br(strip_tags($stackTrace)) ."</p>", $errorPage);
		        }

		        //remove tags for cli mode
		        if (\System\Server\SAPI::getSAPI() == \System\Server\SAPI::SAPI_CLI)
	            {
	            	$errorPage = strip_tags(str_ireplace(array('<br />', '<br>', '<br/>', '</p><p>'), PHP_EOL, $errorPage));
				}
			}
        }

        //terminate; in debug mode we output potentially harmfull info without escaping!
        $errorPage = str_replace('{ERROR}', "<p><b>Details:</b></p><p>(ERR: 1001) The page you requested could not be found. Please contact the webmaster.</p>" . (defined('DEBUG') ? '<p>' . $errorNotice . '</p>' : ''), $errorPage);

        echo $errorPage;
    }

    /**
    * This function is called before the system shuts down. It can catch fatal errors and report those.
    */
    public static final function handleShutdown()
    {
        if ($error = error_get_last())
        {
            switch ($error['type'])
            {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    self::outputError($error['type'], basename($error['file']) . '(' . $error['line'] . ') - ' . $error['message'], $error['file'], $error['line'], '');
                    break;
            }
        }
    }

    /**
    * The custom exception handler. This function will be called automatically, since it is registered with PHP
    * @param \Exception The exception that will be passed to our handler
    * @return bool Always returns false as to convention for exceptionhandlers
    */
    public static final function handleException(\Exception $e)
    {
        $stackTrace = $e->getTraceAsString();

        self::outputError($e->getCode(), get_class($e) . ' - ' . basename($e->getFile()) . '(' . $e->getLine() . ') - ' . $e->getMessage(), $e->getFile(), $e->getLine(), $stackTrace);
        die();

        return false;
    }

    /**
    * The custom error handler. This function will be called automatically, since it is registered with PHP
    * @param int The errornumber, or errorlevel. This indicates the type of error.
    * @param string The errormessage.
    * @param string The file the error occured in
    * @param int The line number of the error.
    * @return bool Always returns false as to convention for errorhandlers
    */
    public static final function handleError($errorNumber, $errorString, $errorFile, $errorLine)
    {
		//if errors are suppressed we continue without handling our error
		if (error_reporting() == 0)
		{
			return true;
		}

    	$traces = debug_backtrace(false);
    	self::outputError($errorNumber, basename($errorFile) . '(' . $errorLine . ') - ' . $errorString, $errorFile, $errorLine, self::getFormattedTraceAsString($traces));
    	die();
        //we use our own implementation of ErrorException, because of a know bug in the default ErrorException class in PHP where the arguments in the stacktrace are messed up.
        //throw new \System\Error\Exception\ErrorExceptionHandler($errorString, $errorNumber, $errorNumber, $errorFile, $errorLine);
        return false;
    }

    /**
    * Our custom assert handler. This function logs the failed assertion and then halts.
    * @param string The file in which the assertion failed
    * @param int The linenumber of the failed assertion
    * @param string The failed expression (optional)
    * @param string The description (optional)
    * @return bool Always false
    */
    public static final function handleAssert($file, $line, $expression = '', $description = '')
    {
        $assertMessage = 'Assertion failed in ' . basename($file) . ':' . $line;
        if (mb_strlen($expression) > 0)
        {
        	$assertMessage .= ': ' . $expression;
		}
        if (mb_strlen($description) > 0)
        {
            $assertMessage .= ': ' . $description;
        }

        $backtrace = debug_backtrace(false);

        self::outputError(E_USER_ERROR, $assertMessage, $file, $line, self::getFormattedTraceAsString($backtrace));
        die();
        return false;
    }

	/**
    * This function removes the top of the stacktrace and formats the trace to a wellformed
    * string stracktrace.
    * @param string The original backtrace
    * @return string The stacktrace
    */
    private static final function getFormattedTraceAsString($traces)
    {
        array_shift($traces);

        $outputString = '';
        foreach ($traces as $index=>$trace)
        {
            $outputString .= '#' . $index . ' ';
            if (isset($trace['file']))
            {
                $outputString .= $trace['file'] . '(';
                if (isset($trace['line']))
                {
                    $outputString .=  $trace['line'];
                }
                $outputString .= '): ';
            }
            else
            {
                $outputString .= '[internal]: ';
            }

            if (isset($trace['object']))
            {
                $outputString .= $trace['object'] . $trace['type'];
            }

            $outputString .= $trace['function'] . '(';

            $params = array();
            if (isset($trace['args']))
            {
                foreach ($trace['args'] as $arg)
                {
                    $type = \System\Type::getType($arg);
                    switch ($type)
                    {
                        case \System\Type::TYPE_BOOLEAN:
                        case \System\Type::TYPE_DOUBLE:
                        case \System\Type::TYPE_INTEGER:
                        case \System\Type::TYPE_NULL:
                            $params[] = $type . '{' . \System\Type::getValue($arg) . '}';
                            break;
                        case \System\Type::TYPE_STRING:
                            $params[] = $type . '{"' . \System\Type::getValue($arg) . '"}';
                            break;
                        default:
                            $params[] = $type;
                    }
                }
            }

            $outputString .= implode(', ', $params);
            $outputString .= ')';

            $outputString .= "\r\n";
        }

        return $outputString;
    }
}
