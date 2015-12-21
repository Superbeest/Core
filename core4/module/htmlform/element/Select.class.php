<?php
/**
* Select.class.php
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
* The select element
* @package \Module\HTMLForm\Element
*/
class Select extends \Module\HTMLForm\HTMLInputFormElement
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
    * Only single options may be selected
    */
    const MULTIPLE_SINGLE       = '';
    /**
    * Multiple elements can be selected
    */
    const MULTIPLE_MULTIPLE     = 'multiple';

    /**
    * @publicget
    * @publicset
    * @var string Specifies that an input element should be disabled when the page loads
    */
    protected $disabled = self::DISABLED_ENABLED;

    /**
    * @publicget
    * @publicset
    * @var string Specifies that multiple options can be selected
    */
    protected $multiple = self::MULTIPLE_SINGLE;

    /**
    * @publicget
    * @publicset
    * @var string Specifies the name of a drop-down list
    */
    protected $name = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the number of visible options in a drop-down list
    */
    protected $size = '';

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
    * @publicget
    * @var \System\Collection\Vector A vector with Option or OptGroups
    */
    protected $options = null;

    /**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that an input field must be filled out before submitting the form.
	*/
    protected $required = false;

    /**
    * Creates a new select object.
    * @param string The name of the element
    */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->options = new \System\Collection\Vector();
    }

    /**
    * Sets the selected value for the selectbox.
    * This function iterates through all the available options and matches its values. If there is a match,
    * that option is set as selected. Wheren there are no option matches, no selected value is chosen and
    * default browser behaviour is used.
    * @param string The value to match
    */
    public function setSelectedValue($value)
    {
		self::setSelectedValueRecursive($this, $value);
    }

    /**
    * Sets the selected value for in all of its child components. This can either be Option objects or OptGroup with Option objects.
    * This function works recursive.
    * @param mixed Either Select instance or OptGroup instance
    * @param string The value to match
    */
    private static function setSelectedValueRecursive($object, $value)
    {
		//both Select and OptGroup support getOptions()
    	foreach ($object->getOptions() as $option)
    	{
			if ($option instanceof OptGroup)
			{
				self::setSelectedValueRecursive($option, $value);
			}
			elseif ($option->getValue() === $value)
		    {
		        $option->setSelected(\Module\HTMLForm\Element\Option::SELECTED_SELECTED);
		        break;
		    }
		}
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
    * Adds an OptGroup element to the current select element
    * @param \Module\HTMLForm\Element\OptGroup The optgroup to add
    */
    public function addOptGroup(\Module\HTMLForm\Element\OptGroup $optGroup)
    {
        $this->options[] = $optGroup;
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
        $child = $xml->addChild('select');
        $child->disabled = $this->disabled;
        $child->multiple = $this->multiple;
        $child->name = $this->name;
        $child->size = $this->size;
        $child->required = $this->required;

        $this->addFields($child);

        $options = $child->addChild('options');

        foreach ($this->options as $option)
        {
            $option->generateXML($options);
        }

        $child->events->onblur = $this->onBlur;
        $child->events->onfocus = $this->onFocus;

        return $child;
    }
}