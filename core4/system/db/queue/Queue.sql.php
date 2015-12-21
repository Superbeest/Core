<?php
/**
* Queue.sql.php
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

namespace System\Db\Queue;

if (!defined('System'))
{
    die ('Hacking attempt');
}

const SQL_QUEUE_ADD = 'INSERT INTO %?% (queue_timestamp, queue_item) VALUES (NOW(), %?%)';
const SQL_QUEUE_PEEK = 'SELECT queue_id AS id, queue_item AS value FROM %?% ORDER BY queue_id ASC LIMIT 1';
const SQL_QUEUE_DELETE = 'DELETE FROM %?% WHERE queue_id = %?% LIMIT 1';
const SQL_QUEUE_COUNT = 'SELECT COUNT(queue_id) AS amount FROM %?%';