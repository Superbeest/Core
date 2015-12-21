<?php
/**
* ErrorLogger.sql.php
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


namespace System\Log;

if (!defined('System'))
{
    die ('Hacking attempt');
}

const SQL_ERRORLOGGER_INSERT = "
INSERT INTO `syserror` (
	`syserror_code`,
	`syserror_string`,
	`syserror_file`,
	`syserror_line`,
	`syserror_timestamp`,
	`syserror_server_ip`,
	`syserror_ip`,
	`syserror_stacktrace`
)
VALUES
	(
		%?%,
		%?%,
		'',
		'0',
		NOW(),
		'',
		'',
		%?%
	)
";