<?php
/**
* Option.class.php
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
* The option element
* @package \Module\HTMLForm\Element
*/
class Option extends \Module\HTMLForm\HTMLFormElement
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
    * The element is not selected
    */
    const SELECTED_UNSELECTED   = '';
    /**
    * The element is selected
    */
    const SELECTED_SELECTED     = 'selected';

    /**
    * @publicget
    * @publicset
    * @var string Specifies that an input element should be disabled when the page loads
    */
    protected $disabled = self::DISABLED_ENABLED;

    /**
    * @publicget
    * @publicset
    * @var string Specifies that an option should be selected by default
    */
    protected $selected = self::SELECTED_UNSELECTED;

    /**
    * @publicget
    * @publicset
    * @var string Specifies the value to be sent to a server when a form is submitted
    */
    protected $value = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the text to be shown for an option
    */
    protected $text = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies a shorter label for an option
    */
    protected $label = '';

    /**
    * Creates a new option element and uses the text as the option text.
    * @param string The text to use as the option text
    */
    public function __construct_1($text = '')
    {
        $this->text = $text;
    }

    /**
    * Creates a new option element and uses the text as the option text, with the given value.
    * @param string The text to use as the option text
    * @param string The text to use as a value
    */
    public function __construct_2($text, $value)
    {
        $this->text = $text;
        $this->value = $value;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('option');
        $child->disabled = $this->disabled;
        $child->selected = $this->selected;
        $child->value = $this->value;
        $child->text = $this->text;
        $child->label = $this->label;

        $this->addFields($child);

        return $child;
    }
}