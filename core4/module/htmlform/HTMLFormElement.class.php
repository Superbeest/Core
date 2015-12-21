<?php
/**
* HTMLFormElement.class.php
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


namespace Module\HTMLForm;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Defines the standard attributes and events for a regular form element
* @package \Module\HTMLForm
*/
abstract class HTMLFormElement extends \Module\HTMLForm\HTMLElement
{
    /**
    * @publicget
    * @publicset
    * @var string Specifies a classname for an element
    */
    protected $class = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the text direction for the content in an element
    */
    protected $dir = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies a unique id for an element
    */
    protected $id = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies an inline style for an element
    */
    protected $style = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies extra information about an element
    */
    protected $title = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when a key is pressed
    */
    protected $onKeyDown = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when a key is pressed and released
    */
    protected $onKeyPress = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when a key is released
    */
    protected $onKeyUp = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run on a mouse click
    */
    protected $onClick = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run on a mouse double-click
    */
    protected $onDblClick = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when mouse button is pressed
    */
    protected $onMouseDown = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when mouse pointer moves
    */
    protected $onMouseMove = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when mouse pointer moves out of an element
    */
    protected $onMouseOut = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when mouse pointer moves over an element
    */
    protected $onMouseOver = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when mouse button is released
    */
    protected $onMouseUp = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies a language code for the content in an element
    */
    protected $lang = '';

	/**
	* @validatehandle
	* @publicset
	* @publicget
	* @var \System\Collection\Map A map with data elements. The key is used as the last part
	*/
    protected $dataElements = null;


    /**
    * Adds standard data to the element.
    * @param \SimpleXMLElement The xml element to expand, by reference
    */
    protected function addFields(\SimpleXMLElement $xml)
    {
        $xml->class = $this->class;
        $xml->dir = $this->dir;
        $xml->id = $this->id;
        $xml->style = $this->style;
        $xml->title = $this->title;
        $xml->lang = $this->lang;

        $xml->events->onkeydown = $this->onKeyDown;
        $xml->events->onkeypress = $this->onKeyPress;
        $xml->events->onkeyup = $this->onKeyUp;
        $xml->events->onclick = $this->onClick;
        $xml->events->ondblclick = $this->onDblClick;
        $xml->events->onmousedown = $this->onMouseDown;
        $xml->events->onmousemove = $this->onMouseMove;
        $xml->events->onmouseout = $this->onMouseOut;
        $xml->events->onmouseover = $this->onMouseOver;
        $xml->events->onmouseup = $this->onMouseUp;

        $dataElements = $this->getDataElements();
        $datasXML = $xml->addChild('datas');
        foreach ($dataElements as $dataElementKey=>$dataElementValue)
        {
        	$dataXML = $datasXML->addChild('data');
        	$dataXML->addAttribute('key', $dataElementKey);
        	$dataXML->addAttribute('value', $dataElementValue);
		}
    }
}
