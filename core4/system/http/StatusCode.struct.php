<?php
/**
* StatusCode.struct.php
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


namespace System\HTTP;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Defines the HTTP statuscodes, see wiki for definitions
* @package \System\HTTP
*/
class StatusCode extends \System\Base\BaseStruct
{
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING = 102;

	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTI_STATUS = 207;
	const HTTP_ALREADY_REPORTED = 208;
	const HTTP_IM_USED = 226;

	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_SWITCH_PROXY = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENT_REDIRECT = 308;

	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const HTTP_EXPECTATION_FAILED = 417;
	const HTTP_IAM_A_TEAPOT = 418;
	const HTTP_ENHANCE_YOUR_CALM = 420;
	const HTTP_UNPROCESSABLE_ENTITY = 422;
	const HTTP_LOCKED = 423;
	const HTTP_FAILED_DEPENDANCY = 424;
	const HTTP_METHOD_FAILURE = 424;
	const HTTP_UNORDERED_COLLECTION = 425;
	const HTTP_UPGRADE_REQUIRED = 426;
	const HTTP_PRECONDITION_REQUIRED = 428;
	const HTTP_TOO_MANY_REQUESTS = 429;
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const HTTP_NO_RESPOSNE = 444;
	const HTTP_RETRY_WITH = 449;
	const HTTP_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
	const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
	const HTTP_REDIRECT = 451;
	const HTTP_REQUEST_HEADER_TOO_LARGE = 494;
	const HTTP_CERT_ERROR = 495;
	const HTTP_NO_CERT = 496;
	const HTTP_HTTP_TO_HTTPS = 497;
	const HTTP_CLIENT_CLOSED_REQUEST = 499;

	const HTTP_INTERNALE_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_HTTP_VERSION_NOT_SUPPORTED = 505;
	const HTTP_VAIRANT_ALSO_NEGOTIATES = 506;
	const HTTP_INSUFFICIENT_STORAGE = 507;
	const HTTP_LOOP_DETECTED = 508;
	const HTTP_BANDWITH_LIMIT_EXCEEDED = 509;
	const HTTP_NOT_EXTENDED = 510;
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
	const HTTP_NETWORK_READ_TIMEOUT_ERROR = 598;
	const HTTP_NETWORK_CONNECT_TIMEOUT_ERROR = 599;
}