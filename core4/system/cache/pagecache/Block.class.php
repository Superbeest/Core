<?php
/**
* Block.class.php
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


namespace System\Cache\PageCache;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* A default content block for data ouput in XML format
* @package \System\Cache\PageCache
*/
class Block extends \System\Collection\Map
{
    /**
    * @var callback The given callback
    */
    protected $callback = '';

    /**
    * @publicget
    * @publicset
    * @var \System\Db\Database The database to use for this block
    */
    protected $database;

    /**
    * Creates a Block object with the given callback
    * @param \System\Db\Database The database to use for the LUT
    * @param callback A callback to call on Block execution
    */
    public function __construct(\System\Db\Database $db, $callback)
    {
        $this->database = $db;
        $this->callback = $callback;
    }

    /**
    * Returns the current callback
    * @return callback The callback to call on Block execution.
    */
    public final function getCallBack()
    {
        return $this->callback;
    }

    /**
    * Calls the corresponding function in the block and executes the data retrieval function.
    * @return \SimpleXMLElement The XML data tree
    */
    public final function callBlock()
    {
        $callback = $this->getCallback();
        if (is_callable($callback))
        {
            return call_user_func($callback, $this);
        }
        else
        {
            throw new \System\Error\Exception\InvalidMethodException('The given callback cannot be called. Does it exist and is it public?');
        }
    }
}