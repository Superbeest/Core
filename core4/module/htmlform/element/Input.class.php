<?php
/**
* Input.class.php
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
* The baseclass for the input elements
* @package \Module\HTMLForm\Element
*/
abstract class Input extends \Module\HTMLForm\HTMLInputFormElement
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
    * @publicget
    * @publicset
    * @var string Specifies that an input element should be disabled when the page loads
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
    * @var string Specifies the width of an input field
    */
    protected $size = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the value of an input element
    */
    protected $value = '';


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
	* @publicget
	* @publicset
	* @var bool When true, it specifies that an <input> element should automatically get focus when the page loads
	*/
    protected $autoFocus = false;

    /**
    * @publicget
    * @publicset
    * @var string The form attribute specifies one or more forms an <input> element belongs to.
    */
    protected $form = '';

    /**
    * Creates an input element with some default values.
    * @param string The name of the input field
    * @param string The value of the input field
    * @param string The id of the input field. This is optional, but required when using Label tags
    */
    public function __construct($name, $value = '', $id = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->id = $id;
    }

    /**
    * Adds standard data to the element.
    * @param \SimpleXMLElement The xml element to expand, by reference
    */
    protected function addFields(\SimpleXMLElement $xml)
    {
        parent::addFields($xml);

        $xml->disabled = $this->disabled;
        $xml->name = $this->name;
        $xml->size = $this->size;
        $xml->value = $this->value;

        $xml->form = $this->form;

        if ($this->autoFocus)
		{
			$xml->autofocus = 'on';
		}

        $xml->events->onblur = $this->onBlur;
        $xml->events->onchange = $this->onChange;
        $xml->events->onfocus = $this->onFocus;
        $xml->events->onselect = $this->onSelect;
    }
}