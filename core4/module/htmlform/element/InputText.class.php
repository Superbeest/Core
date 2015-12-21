<?php
/**
* InputText.class.php
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
* The text input type element
* @package \Module\HTMLForm\Element
*/
class InputText extends \Module\HTMLForm\Element\Input
{
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
    * @var int Specifies the maximum length (in characters) of an input field (for type="text" or type="password")
    */
    protected $maxLength = 255;

    /**
	* @publicget
	* @publicset
	* @var bool True to support autocomplete, false to disable
	*/
    protected $autoComplete = true;

    /**
	* @publicget
	* @publicset
	* @var string The placeholder attribute for input elements
	*/
    protected $placeholder = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies that an input field should be read-only (for type="text" or type="password")
    */
    protected $readonly = self::READONLY_READWRITE;

	/**
	* @publicget
	* @publicset
	* @var string The pattern attribute specifies a regular expression that the <input> element's value is checked against.
	*/
    protected $pattern = '';

	/**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that an input field must be filled out before submitting the form.
	*/
    protected $required = false;

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('input');

        $this->outputType($child);

        $child->maxlength = $this->maxLength;
        $child->readonly = $this->readonly;

        $child->pattern = $this->pattern;
        $child->placeholder = $this->placeholder;
        $child->required = $this->required;

        $this->addFields($child);

        //this needs a different setting
        if (!$this->autoComplete)
        {
        	$xml->autocomplete = 'off';
		}

        return $child;
    }

	/**
	* Outputs the current type to the xml. Override this function to implement
	* your own type
	* @param \SimpleXMLElement The xml tree to append to
	*/
    protected function outputType(\SimpleXMLElement $xml)
    {
    	$xml->addAttribute('type', 'text');
	}
}