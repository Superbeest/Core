<?php
/**
* Legend.class.php
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
* The Legend element
* @package \Module\HTMLForm\Element
*/
class Legend extends \Module\HTMLForm\HTMLFormElement
{
    /**
    * @publicget
    * @publicset
    * @var string The caption of the label
    */
    protected $value = '';

    /**
    * Creates a new legend element. The value is used as the text for the legend.
    * A Legend element can only be used as the first element in a fieldset element
    * @param string The caption for the legend element
    */
    public function __construct($value = '')
    {
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
        $child = $xml->addChild('legend');

        $child->value = $this->value;

        $this->addFields($child);

        return $child;
    }
}