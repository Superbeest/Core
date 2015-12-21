<?php
/**
* Page.class.php
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
* The base class for all pages that work with block elements
* @package \System\Cache\PageCache
*/
abstract class Page extends \System\Base\BaseObj
{
    /**
    * If the 'render' $_GET variable equals to RENDER_XML, we only output the XML instead of the translated variant
    */
    const RENDER_XML = 'xml';

    /**
    * The output renderer for XSL
    */
    const OUTPUT_RENDERER_XSL = 1;
    /**
    * The output renderer for XML
    */
    const OUTPUT_RENDERER_XML = 2;

    /**
    * @publicget
    * @publicset
    * @var int The output renderer to use.
    */
    protected $outputRenderer = \System\Cache\PageCache\Page::OUTPUT_RENDERER_XSL;

    /**
    * @var SimpleXMLElement The assembled XML tree
    */
    private $xml = null;

    /**
    * @var \System\Collection\Vector The vector with all the given blocks
    */
    private $blocks = null;

    /**
    * @var \System\Db\Database The database to query
    */
    private $db = null;

    /**
    * Constructor to set up the page
    * If the $_GET superglobal defiens a 'render' field that is set to \System\Cache\PageCache\Page::RENDER_XML, then the output renderer will be set to
    * \System\Cache\PageCache\Page::OUTPUT_RENDERER_XML. This setting can be overridden lateron.
    * @param \System\Db\Database The database to use
    */
    public final function __construct(\System\Db\Database $db)
    {
        $get = new \System\HTTP\Request\Get();

        if ($get->render == self::RENDER_XML)
        {
            $this->setOutputRenderer(self::OUTPUT_RENDERER_XML);
        }

        $this->blocks = new \System\Collection\Vector();
        $this->xml = \System\XML\XML::createXMLRoot();
        $this->db = $db;

        if (!defined('PAGECACHE_STATICBLOCK_ENABLE'))
        {
            define ('PAGECACHE_STATICBLOCK_ENABLE', true);
        }

        if ((PAGECACHE_STATICBLOCK_ENABLE !== true) &&
            (PAGECACHE_STATICBLOCK_ENABLE !== false))
        {
            throw new \System\Error\Exception\SystemException('PAGECACHE_STATICBLOCK_ENABLE is set to an invalid value. Bool expected');
        }

        $primaryBlock = new \System\Cache\PageCache\Block($db, array($this, 'setup'));
        $this->addBlock($primaryBlock);
    }

    /**
    * This function can be used to mimic the behaviour of a constructor.
    * Override this function and apply your own functionality.
    * By overriding this function, we are able to define a general layout construction.
    * This function always gets called.
    * @param \System\Cache\PageCache\Block The block to base the page on.
    * @return \SimpleXMLElement The XML block to merge into the root
    */
    public function setup(\System\Cache\PageCache\Block $block)
    {
        $xml = \System\XML\XML::createXMLRoot();

        return $xml;
    }

    /**
    * Returns the database given to this page
    * @return \System\Db\Database The database instance
    */
    protected final function getDatabase()
    {
        return $this->db;
    }

    /**
    * Returns the full filename of the XSL as a string. This file may be absolute or relative, as long as its accessable.
    * @return string The full filename to load as primary XSL.
    */
    public abstract function getXSL();

    /**
    * The options and variables used for XSLT processing. These options can be passed to the XSLTRenderer to be included
    * in the final output document. For access to these options during XSLT processing, add the variables to the XSL.
    * @return \System\Collection\Map The options to set
    */
    public abstract function getPageOptions();

    /**
    * Adds a given block to the page for compilation. By adding a block, the result of the given block will
    * be added to the XML structure and can be used for XSL transformations.
    * Children of the Block class can be added. Currently the implementation only supports \System\Cache\PageCache\Block
    * and \System\Cache\PageCache\StaticBlock types.
    * @param \System\Cache\PageCache\Block The  block to add.
    */
    public final function addBlock(\System\Cache\PageCache\Block $block)
    {
        $this->blocks->add($block);
    }

    /**
    * Combines the XML structures from the given Blocks. The XML will be merged with the current existing xml,
    * possibly overriding already defined nodes. Because of the homogenous output of the different types of blocks,
    * the XML can be merged without any conversion.
    * @return \SimpleXMLElement The final output
    */
    private final function generateXML()
    {
        foreach ($this->blocks as $block)
        {
            $xml = null;
            switch (true)
            {
				//because MemcacheBlock is a subclass of Block, we must query this first
            	case $block instanceof \System\Cache\PageCache\MemcacheBlock:
            		$xml = $xml = $block->callMemcacheBlock();
            		break;
                //because StaticBlock is a subclass of Block, we must query this first
                case $block instanceof \System\Cache\PageCache\StaticBlock:
                    //we check if there is a configuration directive to disable staticblocks and use them as regular blocks
                    if (PAGECACHE_STATICBLOCK_ENABLE)
                    {
                        $xml = $block->callStaticBlock();
                    }
                    else
                    {
                        $xml = $block->callBlock();
                    }
                    break;
                case $block instanceof \System\Cache\PageCache\Block:
                    $xml = $block->callBlock();
                    break;
                default:
                    throw new \Exception('Invalid BlockType detected. Corrupt input given.');
            }

            //check if the xml is valid for processing
            if ($xml instanceof \SimpleXMLElement)
            {
                $this->xml = \System\XML\XML::mergeXML($this->xml, $xml);
            }
            else
            {
                throw new \Exception('The given returnvalue is not a XML document tree!');
            }
        }

        return $this->xml;
    }

    /**
    * Cleans the current file cache by deleting unused files.
    * @param \System\Db\Database The database to LUT query
    */
    public static final function cleanPageCache(\System\Db\Database $db)
    {
        $results = \System\Cache\LUTCache\LUTCache::getCache($db);
        $baseFiles = new \System\Collection\Vector();
        foreach ($results as $result)
        {
            $baseFiles[] = basename($result->value);
        }

        $folder = new \System\IO\Directory(\System\Cache\PageCache\StaticBlock::CACHE_CACHEFOLDER);
        $extensions = new \System\Collection\Vector('xml');
        $files = $folder->getFiles($extensions);
        foreach ($files as $file)
        {
            /** @var \System\IO\File */
            $file = $file;

            if (mb_strpos($file->getFilename(), 'PageCache') !== false)
            {
                if (!$baseFiles->contains($file->getFilename()))
                {
                    $file->delete();
                }
            }
        }
    }

    /**
    * Combines the given blocks, and produces a Renderer to be added to a RenderSurface.
    * The Render is ready to be added to the surface.
    * @return \System\Output\Renderer The renderer to use.
    */
    public final function getRenderer()
    {
        $xml = $this->generateXML();

        switch ($this->getOutputRenderer())
        {
            case self::OUTPUT_RENDERER_XML:
                $renderer = new \System\Output\Renderer\XMLRenderer();
                $renderer->render($xml);
                break;
            case self::OUTPUT_RENDERER_XSL:
                //we raise the event so the system can perform any checks on the page before actually rendering
                $event = new Event\OnBeforeRenderEvent();
                $event->setPage($this);
                $event->setXmlTree($xml);
                $event->raise($this);

                $renderer = new \System\Output\Renderer\XSLTRenderer();
                $renderer->render($xml, \System\IO\Directory::getPath($this->getXSL()), $this->getPageOptions());
                break;
            default:
                throw new \InvalidArgumentException('The given output renderer is invalid: ' . $this->getOutputRenderer());
        }

        return $renderer;
    }
}