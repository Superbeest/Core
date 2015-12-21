<?php
/**
* OptGroup.class.php
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
* The optgroup element
* @package \Module\HTMLForm\Element
*/
class OptGroup extends \Module\HTMLForm\HTMLFormElement
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
    * @var string Specifies a shorter label for an option
    */
    protected $label = '';

    /**
    * @publicget
    * @var \System\Collection\Vector A vector with Option or OptGroups
    */
    protected $options = null;

    /**
    * Creates a new optgroup element
    * @param string The label for the optgroup
    */
    public function __construct($label = '')
    {
        $this->label = $label;
        $this->options = new \System\Collection\Vector();
    }

    /**
    * Adds a single \Module\HTMLForm\Element\Option element to the current optgroup
    * @param \Module\HTMLForm\Element\Option The option to add
    */
    public function addOption(\Module\HTMLForm\Element\Option $option)
    {
        $this->options[] = $option;
    }

    /**
    * Adds a Vector with options to the current optgroup. The elements should be of the \Module\HTMLForm\Element\Option type.
    * @param \System\Collection\Vector The map with options to add
    */
    public function addOptions(\System\Collection\Vector $options)
    {
        foreach ($options as $option)
        {
            if ($option instanceof \Module\HTMLForm\Element\Option)
            {
                $this->options[] = $option;
            }
            else
            {
                throw new \Module\HTMLForm\Exception\InvalidHTMLArgumentException('An entry in the Map is not an Option');
            }
        }
    }

    /**
    * Creates -and adds- \Module\HTMLForm\Element\Option elements from a given Map
    * The index of the entry will be used as the value, while the value in the map will be used as the text
    * @param \System\Collection\Map The options to add.
    */
    public function createOptionsFromMap(\System\Collection\Map $options)
    {
        $this->createOptionsFromArray($options->getArrayCopy());
    }

    /**
    * Creates -and adds- \Module\HTMLForm\Element\Option elements from a given array
    * The index of the entry will be used as the value, while the value in the array will be used as the text
    * @param array The options to add.
    */
    public function createOptionsFromArray(array $options)
    {
        foreach ($options as $value=>$optionName)
        {
            $optionName = (string)$optionName;
            $value = (string)$value;

            $option = new \Module\HTMLForm\Element\Option($optionName);
            $option->setValue($value);
            $this->options[] = $option;
        }
    }

    /**
    * Overrides the default getOnInit()
    * @return string The OnInit javascript string
    */
    public function getOnInit()
    {
        $init = '';
        foreach ($this->options as $element)
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
        $child = $xml->addChild('optgroup');
        $child->disabled = $this->disabled;
        $child->label = $this->label;

        $this->addFields($child);

        $options = $child->addChild('options');
        foreach ($this->options as $option)
        {
            $option->generateXML($options);
        }

        return $child;
    }
}