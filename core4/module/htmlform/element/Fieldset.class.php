<?php
/**
* Fieldset.class.php
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
* Implementation of a fieldset element. This element is used to group form elements.
* @package \Module\HTMLForm\Element
*/
class Fieldset extends \Module\HTMLForm\HTMLFormElement
{
    /**
    * @publicget
    * @publicset
    * @var \Module\HTMLForm\Element\Legend The legend of the fieldset
    */
    protected $legend = null;

    /**
    * @publicget
    * @var \System\Collection\Vector A Vector with all the elements
    */
    protected $elements = null;

    /**
    * Creates the fieldset object.
    * @param \System\Collection\Vector The elements in the fieldset
    * @param string The text for the legend tag. This is optional. If left empty, the legend will not be created.
    */
    public function __construct(\System\Collection\Vector $elements, $legendText = '')
    {
        $this->elements = $elements;
        if ($legendText != '')
        {
            $this->addLegendText($legendText);
        }
    }

    /**
    * Adds a legend text. This automatically creates a Legend element.
    * @param string The text to use as the legend
    */
    public function addLegendText($legendText)
    {
        $legend = new \Module\HTMLForm\Element\Legend($legendText);
        $this->legend = $legend;
    }

    /**
    * Adds a \Module\HTMLForm\HTMLFormElement based element to the fieldset.
    * @param \Module\HTMLForm\HTMLFormElement The element to add
    */
    public function addElement(\Module\HTMLForm\HTMLElement $element)
    {
        $this->elements[] = $element;
    }

    /**
    * Overrides the default getOnInit()
    * @return string The OnInit javascript string
    */
    public function getOnInit()
    {
        $init = '';
        foreach ($this->elements as $element)
        {
            $init .= $element->getOnInit();
        }
        return $init . $this->onInit;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('fieldset');

        if ($this->legend instanceof \Module\HTMLForm\Element\Legend)
        {
            $this->legend->generateXML($child);
        }

        $elements = $child->addChild('formelements');
        foreach ($this->elements as $element)
        {
            $element->generateXML($elements);
        }

        $this->addFields($child);

        return $child;
    }
}