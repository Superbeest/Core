<?php
/**
* StaticBlock.class.php
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
* The StaticBlock container
* @package \System\Cache\PageCache
*/
class StaticBlock extends \System\Cache\PageCache\Block
{
    /**
    * @var string Defines the cache folder
    */
    const CACHE_CACHEFOLDER = PATH_PAGECACHE_CACHE;

    /**
    * @var int The time to wait before polling the cache status
    */
    const CACHE_GENERATE_POLL_TIME = 100;

    /**
    * @var int The time to wait while generating, before exiting and throwing an error
    */
    const CACHE_GENERATE_WAIT_THRESHOLD = 1000;

	/**
	* @var string The prefix for the filename of cached items
	*/
    const FILENAME_PREFIX = 'PageCache_';

	/**
	* @var string The filename extension for the pagecache
	*/
    const FILENAME_EXTENSION = '.xml';

    /**
    * @var string The key to reference in the LUT
    */
    protected $key = '';

    /**
    * Builds the container.
    * @param \System\Db\Database The database to use for the LUT
    * @param callback The callback to call on block execution
    * @param string The key to store the block in the LUT
    */
    public function __construct(\System\Db\Database $db, $callback, $key)
    {
        parent::__construct($db, $callback);
        $this->key = $key;
    }

    /**
    * Returns the current key
    * @return string The LUT key
    */
    public final function getLUTKey()
    {
        return $this->key;
    }

    /**
    * Invalidates a static block. This makes sure the block must be regenerated.
    * @param \System\Db\Database The database to use
    * @param string The unique key, given at the creation of the StaticBlock
    */
    public static final function invalidate(\System\Db\Database $db, $key)
    {
		$filename = '';
		if (\System\Cache\LUTCache\LUTCache::retrieve($db, $key, $filename) == \System\Cache\LUTCache\Status::CACHE_HIT)
		{
			$fullFile = self::getWritablePath($filename) . $filename;
	        if (file_exists($fullFile))
	        {
        		$file = new \System\IO\File($fullFile);
        		$file->delete();
			}
		}

        \System\Cache\LUTCache\LUTCache::invalidate($db, $key);
    }

    /**
    * Calls the corresponding function in the block and checks for LUT availability.
    * @return \SimpleXMLElement The XML data tree
    */
    public final function callStaticBlock()
    {
        $db = $this->getDatabase();

        $xml = '';

        $filename = null;
        $returnVal = \System\Cache\LUTCache\LUTCache::retrieve($db, $this->getLUTKey(), $filename);
        switch ($returnVal)
        {
            case \System\Cache\LUTCache\Status::CACHE_GENERATING:
                $totalWaitTime = 0;
                while (($returnVal = \System\Cache\LUTCache\LUTCache::retrieve($db, $this->getLUTKey(), $filename)) == \System\Cache\LUTCache\Status::CACHE_GENERATING)
                {
                    //we wait before polling the system again
                    usleep(self::CACHE_GENERATE_POLL_TIME);
                    $totalWaitTime += self::CACHE_GENERATE_POLL_TIME;

                    if ($totalWaitTime >= self::CACHE_GENERATE_WAIT_THRESHOLD)
                    {
                        throw new \System\Error\Exception\LUTCacheTimeoutException('The generation timeout is exceeded for block: ' . $this->getLUTKey());
                    }
                }

                if ($returnVal != \System\Cache\LUTCache\Status::CACHE_HIT)
                {
                    throw new \Exception('Invalid LUT returnvalue given after generation: ' . $returnVal);
                }
                //we do a fallthrough after we are done generating
            case \System\Cache\LUTCache\Status::CACHE_HIT:
                //get stuff
                $fullFile = self::getWritablePath($filename) . $filename;
                if (file_exists($fullFile))
                {
					//suppress warnings here in case of malformed xml
                    if ($xml = @simplexml_load_file($fullFile))
                    {
                    	break;
					}
					else
					{
						$errorLogger = \System\Log\ErrorLogger::getInstance();
						$errorLogger->out('[StaticBlock] Could not read ' . $fullFile . ' as XML. Regenerating.', \System\Log\LoggerLevel::LEVEL_WARNING);
					}
                }
                //if the file does not exist, we do a fallthrough to the generation
            case \System\Cache\LUTCache\Status::CACHE_MISS:
            case \System\Cache\LUTCache\Status::CACHE_INVALIDATED:
                //set to currently generating
                \System\Cache\LUTCache\LUTCache::setToGenerate($db, $this->getLUTKey());

                //get the XML
                $xml = $this->callBlock($this);

                //store the xml to a file and add the file to the LUT
                $cacheFile = self::FILENAME_PREFIX . uniqid() . self::FILENAME_EXTENSION;
                $fullFile = self::getWritablePath($cacheFile) . $cacheFile;
                $xml->asXML($fullFile);
                \System\Cache\LUTCache\LUTCache::store($db, $this->getLUTKey(), $cacheFile);
                break;
            default:
                throw new \Exception('Invalid LUT returnvalue given: ' . $returnVal);
        }

        return $xml;
    }

	/**
	* Applies the FileDirector functionality
	* @param string The filename to work with
	* @return string The directory to use as a base, with trailing separator
	*/
    private static function getWritablePath($filename)
    {
		//reverse the randomized filename to make a more unique container
    	$container = strrev(substr($filename, strlen(self::FILENAME_PREFIX), -strlen(self::FILENAME_EXTENSION))); //remove the extension
    	$folder = new \System\IO\Directory(self::CACHE_CACHEFOLDER);
    	return \System\IO\FileDirector::getWritablePath($folder, $container)->getCurrentPath(true);
	}
}