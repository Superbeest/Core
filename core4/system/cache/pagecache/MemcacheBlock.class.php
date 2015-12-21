<?php
/**
* MemcacheBlock.class.php
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
* Implements the Block system with memcache caching features
* @package \System\Cache\PageCache
*/
class MemcacheBlock extends \System\Cache\PageCache\Block
{
	/**
	* The default timeout
	*/
	const TIMEOUT_DEFAULT = 600;

	/**
	* @publicget
	* @publicset
	* @var int The timeout for this block
	*/
	protected $timeout = self::TIMEOUT_DEFAULT;

	/**
    * @publicget
    * @var string The key to reference
    */
    protected $key = '';

 	/**
    * Builds the container.
    * @param \System\Db\Database The database to use for the LUT
    * @param callback The callback to call on block execution
    * @param string The unique key, given at the creation of the MemcacheBlock
    * @param int The default timeout for the cacheblock
    */
	public function __construct(\System\Db\Database $db, $callback, $key, $timeout = \System\Cache\PageCache\MemcacheBlock::TIMEOUT_DEFAULT)
    {
        parent::__construct($db, $callback);
        $this->key = $key;
        $this->timemout = $timeout;
    }

	/**
    * Calls the corresponding function in the block and executes the data retrieval function.
    * @return \SimpleXMLElement The XML data tree
    */
    public final function callMemcacheBlock()
    {
        $callback = $this->getCallback();
		$db = $this->getDatabase();

		$mem = new \System\Cache\Memcache\Memcache();

		$xml = null;
		if ($mem->keyExists($this->key))
		{
			$xmlString = $mem->get($this->key);
			if ($xmlString)
			{
				//suppress warning here in case of malformed xml
				$xml = @simplexml_load_string($xmlString);
				if ($xml instanceof \SimpleXMLElement)
				{
					return $xml;
				}
				else
				{
					$errorLogger = \System\Log\ErrorLogger::getInstance();
					$errorLogger->out('[MemcacheBlock] Could not read memcache key ' . $this->key . ' as XML. Regenerating.', \System\Log\LoggerLevel::LEVEL_WARNING);
				}
			}
		}

		if (is_callable($callback))
        {
            $xml = call_user_func($callback, $this);
            $mem->store($this->key, $xml->asXML(), $this->timeout);
        }
        else
        {
            throw new \System\Error\Exception\InvalidMethodException('The given callback cannot be called. Does it exist and is it public?');
        }

        return $xml;
    }
}