<?php
/**
* XML.class.php
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


namespace System\XML;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Basic XML creation modification functions
* @package \System\XML
*/
class XML extends \System\Base\StaticBase
{
    /**
    * Creates a SimpleXMLElement root.
    * @return \SimpleXMLElement The XML root
    */
    public static final function createXMLRoot()
    {
        return simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><document />');
    }

    /**
    * Combine two XML trees into one tree.
    * @param \SimpleXMLElement The first tree
    * @param \SimpleXMLElement The second tree
    * @return \SimpleXMLElement The merged XML tree
    */
    public static final function mergeXML(\SimpleXMLElement $xml1, \SimpleXMLElement $xml2)
    {
        $node = dom_import_simplexml($xml1);
        $dom1 = $node->ownerDocument;

        $node = dom_import_simplexml($xml2);
        $dom2 = $node->ownerDocument;

        $xpath = new \DOMXPath($dom2);
        $query = $xpath->query('/*/*');

        for ($i = 0; $i < $query->length; $i++)
        {
            $dom1->documentElement->appendChild($dom1->importNode($query->item($i), true));
        }

        return simplexml_import_dom($dom1);
    }

    /**
    * Converts a given \SimpleXMLElement XML tree to the DOMDocument format.
    * This conversion is relatively fast, because of the overlapping internal structure of the types.
    * @param \SimpleXMLElement The XML tree to transform
    * @return \DOMDocument The converted XML tree
    */
    public static final function convertSimpleXMLElementToDOMDocument(\SimpleXMLElement $xml)
    {
        $node = dom_import_simplexml($xml);
        return $node->ownerDocument;
    }

    /**
    * Adds a CData section to the given XML tree
    * @param \SimpleXMLElement The XML tree to manipulate
    * @param string The CData text to add
    */
    public static final function addCDataToXML(\SimpleXMLElement $xml, $cdataText)
    {
        $node = dom_import_simplexml($xml);
        $nodeOwner = $node->ownerDocument;

        $node->appendChild($nodeOwner->createCDATASection($cdataText));
    }

	/**
	* Loads a \SimpleXMLElement from the given data.
	* @param string The data to load as XML
	* @param bool On true, ignores parse errors
	* @return \SimpleXMLElement The XML tree, or false on failure
	*/
    public static final function loadXMLString($data, $ignoreErrors = true)
    {
    	$previousSetting = false;

    	if ($ignoreErrors)
    	{
    		$previousSetting = libxml_use_internal_errors(true);
		}

		$xml = simplexml_load_string($data);

		if ($ignoreErrors)
    	{
    		libxml_use_internal_errors($previousSetting);
		}

		if (($xml) &&
			($xml instanceof \SimpleXMLElement))
		{
			return $xml;
		}

		return false;
	}

	/**
	* Loads a \SimpleXMLElement from the given data.
	* @param string A file to load, or an URL to a remote source
	* @param bool On true, ignores parse errors
	* @return \SimpleXMLElement The XML tree, or false on failure
	*/
	public static final function loadXMLFile($url, $ignoreErrors = true)
	{
		$previousSetting = false;

    	if ($ignoreErrors)
    	{
    		$previousSetting = libxml_use_internal_errors(true);
		}

		if ($ignoreErrors)
		{
			$xml = @simplexml_load_file($url);
		}
		else
		{
			$xml = simplexml_load_file($url);
		}

		if ($ignoreErrors)
    	{
    		libxml_use_internal_errors($previousSetting);
		}

		if (($xml) &&
			($xml instanceof \SimpleXMLElement))
		{
			return $xml;
		}

		return false;
	}

	/**
	* Loads html content into a simplexml structure.
	* We use default utf-8 encoding if it cannot be detected.
	* If the loading of the dom cannot be done, we return an empty tree
	* @param string The html content to load
	* @return \SimpleXMLElement The simple xml element tree,
	*/
	public static final function loadHTML($content)
	{
		$encoding = mb_detect_encoding($content);
		if (!$encoding)
		{
			//we use default utf-8 encoding if it cannot be detected
			$encoding = 'utf-8';
		}

		$previousSetting = libxml_use_internal_errors(true);

		$dom = new \DOMDocument(null, $encoding);
		if (@$dom->loadHTML($content)) //we suppress the warnings here, as they cannot be controlled by libxml
		{
			$xml = @simplexml_import_dom($dom);
			if (!$xml)
			{
				$xml = self::createXMLRoot();
			}
		}
		else
		{
			$xml = self::createXMLRoot();
		}

		libxml_use_internal_errors($previousSetting);

		return $xml;
	}

	/**
	* Appends the given $element node to the given $to element.
	* This is added directly to the $to element.
	* @param \SimpleXMLElement The element to be mutated and expanded
	* @param \SimpleXMLElement The element to add to the target element
	*/
	public static final function appendToXML(\SimpleXMLElement $to, \SimpleXMLElement $element)
	{
		$toDom = dom_import_simplexml($to);
		$dom1 = $toDom->ownerDocument;

		$fromDom = dom_import_simplexml($element);
		$toDom->appendChild($dom1->importNode($fromDom, true));
	}
}