<?php
/**
* Label.class.php
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
* The label element
* @package \Module\HTMLForm\Element
*/
class Label extends \Module\HTMLForm\HTMLFormElement
{
    /**
    * @publicget
    * @publicset
    * @var \Module\HTMLForm\HTMLElement The element to refer to
    */
    protected $element = null;

    /**
    * @publicget
    * @publicset
    * @var string The caption of the label
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
    * @var string Script to be run when an element gets focus
    */
    protected $onFocus = '';

    /**
    * Creates a new label element. The element attached to this label should have the id property set.
    * @param \Module\HTMLForm\HTMLElement The element to create the label to.
    * @param string The text for the label
    */
    public function __construct(\Module\HTMLForm\HTMLElement $element, $value = '')
    {
        $this->element = $element;
        $this->value = $value;
    }

    /**
    * Overrides the default getOnInit()
    * @return string The OnInit javascript string
    */
    public function getOnInit()
    {
        return $this->element->getOnInit() . $this->onInit;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('label');

        if (!($this->element instanceof \Module\HTMLForm\HTMLElement))
        {
            throw new \Module\HTMLForm\Exception\MissingRequiredFieldException('Required inputElement is not a valid HTMLElement');
        }

        if (mb_strlen($this->element->getId()) == 0)
        {
            throw new \Module\HTMLForm\Exception\MissingRequiredFieldException('Required inputElement has no id tag');
        }

        $elements = $child->addChild('formelements');
        $this->element->generateXML($elements);

        $child->value = $this->value;
        $child->for = $this->element->getId();

        $child->events->onblur = $this->onBlur;
        $child->events->onfocus = $this->onFocus;

        $this->addFields($child);

        return $child;
    }
}