<?php
/**
* BasePage.page.php
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


namespace System\Web\BasePage\Page;

if (!defined('System'))
{
	die ('Hacking attempt');
}

/**
* A BasePage implementation
* @package \System\Web\BasePage\Page
*/
abstract class BasePage extends \System\Cache\PageCache\Page
{
	/**
	* The extension used for CSS files
	*/
	const CSS_EXTENSION = '.css';
	/**
	* The extension used for minified CSS files
	*/
	const CSS_MIN_EXTENSION = '.min.css';

	/**
	* The extension used for JS files
	*/
	const JS_EXTENSION = '.js';
	/**
	* The extension used for minified JS files
	*/
	const JS_MIN_EXTENSION = '.min.js';

	/**
	* Reserved word for packed files
	*/
	const JS_MIN_PACKED = 'pack';

	/*
	* The CSS media directives
	*/
	/**
	* Used for all media type devices
	*/
	const CSS_MEDIA_ALL = 'all';
	/**
	* Used for speech and sound synthesizers
	*/
	const CSS_MEDIA_AURAL = 'aural';
	/**
	* Used for braille tactile feedback devices
	*/
	const CSS_MEDIA_BRAILLE = 'braille';
	/**
	* Used for paged braille printers
	*/
	const CSS_MEDIA_EMBOSSED = 'embossed';
	/**
	* Used for small or handheld devices
	*/
	const CSS_MEDIA_HANDHELD = 'handheld';
	/**
	* Used for printers
	*/
	const CSS_MEDIA_PRINT = 'print';
	/**
	* Used for projected presentations, like slides
	*/
	const CSS_MEDIA_PROJECTION = 'projection';
	/**
	* Used for computer screens
	*/
	const CSS_MEDIA_SCREEN = 'screen';
	/**
	* Used for media using a fixed-pitch character grid, like teletypes and terminals
	*/
	const CSS_MEDIA_TTY = 'tty';
	/**
	* Used for television-type devices
	*/
	const CSS_MEDIA_TV = 'tv';

	/**
	* The default CSS rel type
	*/
	const CSS_REL_DEFAULT = 'stylesheet';
	/**
	* The LESS CSS rel type
	*/
	const CSS_REL_LESS = 'stylesheet/less';

	/**
	* The amount of words in the XML description block
	*/
	const DESCRIPTION_XML_WORD_AMOUNT = 20;

	/**
	* @publicset
	* @publicget
	* @var string The name of the page
	*/
	protected $title = '';
	/**
	* @publicset
	* @publicget
	* @var string The keywords of the page
	*/
	protected $keywords = '';
	/**
	* @publicset
	* @publicget
	* @var string The description of the page
	*/
	protected $description = '';
	/**
	* @publicget
	* @publicset
	* @var string The name of the company producing the website
	*/
	protected $company = 'SUPERHOLDER';

	/**
	* The options and variables used for XSLT processing. These options can be passed to the XSLTRenderer to be included
	* in the final output document. For access to these options during XSLT processing, add the variables to the XSL.
	* @return \System\Collection\Map The options to set
	*/
	public function getPageOptions()
	{
            $map = new \System\Collection\Map();
            $map->title = $this->getTitle();
            $map->keywords = $this->getKeywords();
            $map->description = $this->getDescription();

            $map->publicRoot = PUBLIC_ROOT;
            $map->queryTime = array('\System\Db\Database', 'getTotalQueryTime');
            $map->queryAmount = array('\System\Db\Database', 'getTotalQueryCount');
            $map->executionTime = array('\System\Calendar\Timer', 'getSystemExecutionTime');
            $map->sessionHandler = array('\System\HTTP\Storage\Session', 'getCurrentHandler');
            $map->debugMode = defined('DEBUG');
            $map->language = \System\Internationalization\Language::c_getPrimaryLanguage(\System\Internationalization\Language::c_getLanguage());
            $map->company = $this->getCompany();

            $map->browser = \System\HTTP\Visitor\Browser::c_getBrowser();
            $map->browserVersion = \System\HTTP\Visitor\Browser::c_getBrowserVersion();

            return $map;
	}

	/**
	* Sets the title for the XML document. This gets added under the root.
	* Expects a title entry in the block
	* @param \System\Cache\PageCache\Block The block with the title entry
	* @return \SimpleXMLElement The XML tree
	*/
	public function outputTitleXML(\System\Cache\PageCache\Block $block)
	{
            $xml = \System\XML\XML::createXMLRoot();

            $xml->title = \System\Security\Sanitize::sanitizeString((string)$block->title, false, false, true, true, false, false);

            return $xml;
	}

	/**
	* Sets the description for the XML document. This gets added under the root.
	* Expects a description entry in the block
	* Accepts an optional amount entry in the block
	* @param \System\Cache\PageCache\Block The block with the description entry
	* @return \SimpleXMLElement The XML tree
	*/
	public function outputDescriptionXML(\System\Cache\PageCache\Block $block)
	{
            $xml = \System\XML\XML::createXMLRoot();

            $description = \System\Security\Sanitize::sanitizeString((string)$block->description, false, false, true, true, false, false);

            $words = str_word_count($description, 1);
            $amount = self::DESCRIPTION_XML_WORD_AMOUNT;

            if (isset($block->amount))
            {
                $amount = (int)$block->amount;
            }

            $words = array_slice($words, 0, $amount);

            $desc = implode(' ', $words);

            if (strlen($desc) < strlen($description))
            {
                $desc .= '...';
            }

            $xml->description = $desc;

            return $xml;
	}

	/**
	* Sets the keywords for the XML document. This gets added under the root.
	* Expects a keywords entry in the block
	* @param \System\Cache\PageCache\Block The block with the keywords entry
	* @return \SimpleXMLElement The XML tree
	*/
	public function outputKeywordsXML(\System\Cache\PageCache\Block $block)
	{
            $xml = \System\XML\XML::createXMLRoot();

            $xml->keywords = \System\Security\Sanitize::sanitizeString((string)$block->keywords, false, false, true, true, false, false);

            return $xml;
	}

	/**
	* Adds an entry to the breadcrumb
	* @param \SimpleXMLElement The xml to append to
	* @param string The text to use for the breadcrumb
	* @param string The link to make the crumb target to
	*/
	public function addToBreadCrumb(\SimpleXMLElement $xml, $text, $link)
	{
            if (!isset($xml->breadcrumbs))
            {
                $xml->addChild('breadcrumbs');
            }
            $crumbs = $xml->breadcrumbs;

            $c = $crumbs->addChild('crumb');
            $c->name = $text;
            $c->link = $link;
	}

	/**
	* Adds a CSS file to be loaded upon clientside rendering. Leave the localpath empty for remote files
	* @param \SimpleXMLElement The XML root
	* @param string The (full)path to the local CSS file.
	* @param string The (full)path to the remote file
	* @param string The media type, defaults to CSS_MEDIA_SCREEN
	* @param string The reltype, defaults to CSS_REL_DEFAULT
    * @param int \System\Web\BasePage\Page\InclusionLocation location for placement
    * @param string the crossOrigin attribute can be set. For more information see: https://www.w3.org/TR/SRI/
    * @param string the integrity attribute which is used to check if a remote resource is tempered with in combination with the crossorigin tag
	*/
	public function addCSSFile(\SimpleXMLElement $xml, $localFile, $remoteFile, $media = \System\Web\BasePage\Page\BasePage::CSS_MEDIA_SCREEN, $rel = \System\Web\BasePage\Page\BasePage::CSS_REL_DEFAULT, $location = \System\Web\BasePage\Page\InclusionLocation::LOCATION_HEAD, $crossOrigin = '', $integrity = '')
	{
        if (!isset($xml->cssfiles))
        {
            $xml->addChild('cssfiles');
        }
        $cssFiles = $xml->cssfiles;

        $cssFile = $cssFiles->addChild('cssfile');
        $cssFile->addChild('media', $media);
        $cssFile->addChild('rel', $rel);

        if ($localFile)
        {
            $file = new \System\IO\File($localFile);
            if ((('.' . $file->getExtension()) == self::CSS_EXTENSION) && //only try to minify css files, not less files
                (MINIFY_ENABLE) &&
                (mb_strpos($file->getFilename(), self::CSS_MIN_EXTENSION) === false)) //it is not already minified
            {
                $localMinFile = $file->getPath() . basename($file->getFilename(), self::CSS_EXTENSION) . self::CSS_MIN_EXTENSION;
                if (!file_exists($localMinFile))
                {
                    $file = \System\IO\File::writeContents($localMinFile, \System\Web\Minify\CSS\Minify::minify($file->getContents()));
                }
                else
                {
                    $file = new \System\IO\File($localMinFile);
                }
                $remoteFile = str_ireplace(self::CSS_EXTENSION, self::CSS_MIN_EXTENSION, $remoteFile);
            }
            $cssFile->addChild('filesize', $file->getFileSizeInBytes());
        }
        $cssFile->addChild('name', $remoteFile);
        $cssFile->addChild('location', $location);
        if ($crossOrigin && $integrity)
        {
            $cssFile->addChild('crossorigin', $crossOrigin);
            $cssFile->addChild('integrity', $integrity);
		}
	}

	/**
	* Adds extra meta tags to the basepage
	* @param \SimpleXMLElement The XML root
	* @param string The name of the meta
	* @param string The property of the meta
	* @param string The content of the meta
	* @param string The scheme of the meta
	*/
	public function addCustomMeta(\SimpleXMLElement $xml, $name = '', $property = '', $content = '', $httpEquiv = '', $scheme = '')
	{
            if (!isset($xml->custommetas))
            {
                $xml->addChild('custommetas');
            }
            $metas = $xml->custommetas;

            $meta = $metas->addChild('meta');
            $meta->addChild('property', $property);
            $meta->addChild('name', $name);
            $meta->addChild('http-equiv', $httpEquiv);

            $content = \System\Security\Sanitize::sanitizeString((string)$content, true, false, true, true, false, false);

            $meta->addChild('content', $content);
            $meta->addChild('scheme', $scheme);
	}

	/**
	* Adds an extra link element to the basepage output
	* @see http://www.w3schools.com/tags/tag_link.asp for documentation
	* @param \SimpleXMLElement The XML root
	* @param string $rel
	* @param string $type
	* @param string $href
	* @param string $media
	* @param string $charset
	* @param string $hreflang
	* @param string $rev
	* @param string $sizes
	* @param string $target
	*/
	public function addCustomLink(\SimpleXMLElement $xml, $rel = '', $type = '', $href = '', $media = '', $charset = '', $hreflang = '', $rev = '', $sizes = '', $target = '')
	{
            if (!isset($xml->customlinks))
            {
                $xml->addChild('customlinks');
            }
            $links = $xml->customlinks;

            $link = $links->addChild('link');
            $link->addChild('rel', $rel);
            $link->addChild('type', $type);
            $link->addChild('href', $href);
            $link->addChild('media', $media);
            $link->addChild('charset', $charset);
            $link->addChild('hreflang', $hreflang);
            $link->addChild('rev', $rev);
            $link->addChild('sizes', $sizes);
            $link->addChild('target', $target);
	}

	/**
	* Adds a JS file to be loaded upon clientside rendering. Leave the localpath empty for remote files
	* @param \SimpleXMLElement The XML root
	* @param string The (full)path to the local JS file
	* @param string The (full)path to the remote file
    * @param int The \System\Web\BasePage\Page\InclusionLocation for inclusion location
    * @param string the crossOrigin attribute can be set. For more information see: https://www.w3.org/TR/SRI/
    * @param string the integrity attribute which is used to check if a remote resource is tempered with in combination with the crossorigin tag
	*/
	public function addJSFile(\SimpleXMLElement $xml, $localFile, $remoteFile, $location = \System\Web\BasePage\Page\InclusionLocation::LOCATION_HEAD, $crossOrigin = '', $integrity = '')
	{
        if (!isset($xml->jsfiles))
        {
            $xml->addChild('jsfiles');
        }
        $jsFiles = $xml->jsfiles;

        $jsFile = $jsFiles->addChild('jsfile');
        if ($localFile)
        {
            $file = new \System\IO\File($localFile);
            if ((MINIFY_ENABLE) &&
                (mb_strpos($file->getFilename(), self::JS_MIN_EXTENSION) === false) && //it is not already minified
                (mb_strpos($file->getFilename(), self::JS_MIN_PACKED) === false)) //it is not packed
            {
                $localMinFile = $file->getPath() . basename($file->getFilename(), self::JS_EXTENSION) . self::JS_MIN_EXTENSION;
                if (!file_exists($localMinFile))
                {
                    $file = \System\IO\File::writeContents($localMinFile, \System\Web\Minify\JS\Minify::minify($file->getContents()));
                }
                else
                {
                    $file = new \System\IO\File($localMinFile);
                }
                $remoteFile = str_ireplace(self::JS_EXTENSION, self::JS_MIN_EXTENSION, $remoteFile);
            }
            $jsFile->addChild('filesize', $file->getFileSizeInBytes());
        }
        $jsFile->addChild('name', $remoteFile);
        $jsFile->addChild('location', $location);
        if ($crossOrigin && $integrity)
        {
            $jsFile->addChild('crossorigin', $crossOrigin);
            $jsFile->addChild('integrity', $integrity);
		}
	}

	/**
	* Adds a custom block of code to the head of the html page.
	* This can be used to implement browser specific tags or other elements
	* Note: it can break your html as it is unprotected
	* @param \SimpleXMLElement The XML root
	* @param string The block to add
	*/
	public function addCustomHeadBlock(\SimpleXMLElement $xml, $block)
	{
            if (!isset($xml->customheadblocks))
            {
                $xml->addChild('customheadblocks');
            }
            $customHeadBlock = $xml->customheadblocks;

            $customHeadBlock->addChild('customheadblock', $block);
	}
}
