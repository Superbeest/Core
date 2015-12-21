<?php
/**
* TextArea.class.php
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


namespace Module\HTMLForm\Element;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* The textarea element
* @package \Module\HTMLForm\Element
*/
class TextArea extends \Module\HTMLForm\HTMLInputFormElement
{
    /**
    * Element is enabled
    */
    const DISABLED_ENABLED      = '';
    /**
    * Element is disabled
    */
    const DISABLED_DISABLED     = 'disabled';

    /**
    * Element can be read and written
    */
    const READONLY_READWRITE    = '';
    /**
    * Element is readonly
    */
    const READONLY_READ         = 'readonly';

    /**
    * @publicget
    * @publicset
    * @var int Specifies the visible width of a text-area
    */
    protected $cols = 30;

    /**
    * @publicget
    * @publicset
    * @var int Specifies the visible number of rows in a text-area
    */
    protected $rows = 5;

    /**
    * @publicget
    * @publicset
    * @var string Specifies that a text-area should be disabled
    */
    protected $disabled = self::DISABLED_ENABLED;

    /**
    * @publicget
    * @publicset
    * @var string Specifies the name for a text-area
    */
    protected $name = '';

	/**
	* @publicget
	* @publicset
	* @var string The placeholder attribute for input elements
	*/
    protected $placeholder = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies that a text-area should be read-only
    */
    protected $readonly = self::READONLY_READWRITE;

    /**
    * @publicget
    * @publicset
    * @var string Specifies the content of the textarea
    */
    protected $value = '';

	/**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that the textarea must be filled out before submitting the form.
	*/
    protected $required = false;

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when an element loses focus
    */
    protected $onBlur = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when an element change
    */
    protected $onChange = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when an element gets focus
    */
    protected $onFocus = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when an element is selected
    */
    protected $onSelect = '';

    /**
    * Creates a new TextArea element with a given name
    * @param string The name of the element
    */
    public function __construct($name, $value = '', $id = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->id = $id;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('textarea');
        $child->cols = $this->cols;
        $child->rows = $this->rows;

        $child->value = $this->value;

        $child->disabled = $this->disabled;
        $child->name = $this->name;
        $child->readonly = $this->readonly;
		$child->required = $this->required;
		$child->placeholder = $this->placeholder;

        $this->addFields($child);

        $child->events->onblur = $this->onBlur;
        $child->events->onchange = $this->onChange;
        $child->events->onfocus = $this->onFocus;
        $child->events->onselect = $this->onSelect;

        return $child;
    }
}
