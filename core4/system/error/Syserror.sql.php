<?php
/**
* Syserror.sql.php
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

const SQL_SYSERROR_TOTALCOUNT = 'SELECT COUNT(syserror_id) AS amount FROM syserror';
const SQL_LOG_SYSERROR_DELETE = 'DELETE FROM syserror WHERE syserror_id = %?%';
