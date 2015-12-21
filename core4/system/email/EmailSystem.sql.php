<?php
/**
* EmailSystem.sql.php
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


namespace System\Email;

if (!defined('System'))
{
    die ('Hacking attempt');
}

const SQL_EMAILSYSTEM_ADDMAIL = 'INSERT INTO %?% (`from`, `to`, `cc`, `bcc`, `subject`, `message`, `timeadded`) VALUES (%?%, %?%, %?%, %?%, %?%, %?%, NOW())';
const SQL_EMAILSSYTEM_DELETEMAIL = 'DELETE FROM %?% WHERE id = %?% LIMIT 1';

const SQL_EMAILSYSTEM_GET_HIGH = 'SELECT * FROM queue_high';
const SQL_EMAILSYSTEM_GET_NORMAL = 'SELECT * FROM queue_normal LIMIT %?%';
const SQL_EMAILSYSTEM_GET_LOW = 'SELECT * FROM queue_low LIMIT %?%';

const SQL_EMAILSYSTEM_ADD_ATTACH = 'INSERT INTO attach (attach_blob, attach_filename, attach_mime, attach_mail_id, attach_priority) VALUES (%?%, %?%, %?%, %?%, %?%)';
const SQL_EMAILSYSTEM_GET_ATTACH = 'SELECT * FROM attach WHERE attach_mail_id = %?% AND attach_priority = %?%';
const SQL_EMAILSYSTEM_DELETEATTACH = 'DELETE FROM attach WHERE attach_mail_id = %?% AND attach_priority = %?%';