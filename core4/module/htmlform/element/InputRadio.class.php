<?php
/**
* InputRadio.class.php
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
* The radio type input element
* @package \Module\HTMLForm\Element
*/
class InputRadio extends \Module\HTMLForm\Element\Input
{
    /**
    * The element is checked
    */
    const CHECKED_CHECKED       = 'checked';
    /**
    * The element is not checked
    */
    const CHECKED_UNCHECKED     = '';

    /**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that an input field must be filled out before submitting the form.
	*/
    protected $required = false;

    /**
    * @publicget
    * @publicset
    * @var string Specifies that an input element should be preselected when the page loads (for type="checkbox" or type="radio")
    */
    protected $checked = self::CHECKED_UNCHECKED;

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('input');
        $child->addAttribute('type', 'radio');

        $child->checked = $this->checked;
        $child->required = $this->required;

        $this->addFields($child);

        return $child;
    }
}