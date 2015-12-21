<?php
/**
* Rating.class.php
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


namespace Module\HTMLForm\SpecialElement;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Implements a Rating component
* @package Module\HTMLForm\SpecialElement
*/
class Rating extends \Module\HTMLForm\HTMLElement
{
    /**
    * Default starsplit
    */
    const STARSPLIT_DEFAULT = 2;

    /**
    * Amount of options
    */
    const OPTION_AMOUNT_DEFAULT = 10;

    /**
    * Default selected
    */
    const SELECTED_DEFAULT = 5;

    /**
    * Default with of a star
    */
    const STARWIDTH_DEFAULT = 16;

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
    * @var string Specifies the name for the rating
    */
    protected $name = '';

    /**
    * @publicget
    * @publicset
    * @var int The selected value
    */
    protected $selectedValue = self::SELECTED_DEFAULT;

    /**
    * @publicget
    * @publicset
    * @var int The starsplits
    */
    protected $starSplit = self::STARSPLIT_DEFAULT;

    /**
    * @publicget
    * @publicset
    * @var int The width of a star
    */
    protected $starWidth = self::STARWIDTH_DEFAULT;

    /**
    * @publicget
    * @publicset
    * @var int The amount of options
    */
    protected $optionAmount = self::OPTION_AMOUNT_DEFAULT;

    /**
    * The default prototype of the callback should be in the following format:
    * function(value, link){ alert(value);}
    *
    * @publicget
    * @publicset
    * @var string Script to be run on a mouse click
    */
    protected $onClick = '';

    /**
    * Creates the rating element
    * @param string The name of the element
    */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
    * Return the id of the rating element in this case we always return the name
    */
    public function getId()
    {
        return $this->name;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('rating');

        $child->disabled = $this->disabled;
        $child->name = $this->name;
        $child->starsplit = $this->starSplit;

        if ($this->selectedValue > $this->optionAmount)
        {
            $this->selectedValue = ceil($this->optionAmount / 2);
        }

        //when the rating is disabled, we dont display the radio's. just the images
        if ($this->disabled == self::DISABLED_DISABLED)
        {
            $child->selectedwidth = floor(($this->selectedValue / $this->starSplit) * $this->starWidth);
            $child->unselectedwidth = ceil((($this->optionAmount - $this->selectedValue) / $this->starSplit) * $this->starWidth);
            $child->unselectedoffset = ceil(((int)$child->selectedwidth % $this->starWidth));
            $child->starwidth = $this->starWidth;
        }
        else
        {
            if (!empty($this->onClick))
            {
                $this->onInit .= "$('.star').rating({ callback: " . $this->onClick . "});";
            }

            $elements = $child->addChild('elements');
            for ($x = 1; $x <= $this->optionAmount; $x++)
            {
                $option = $elements->addChild('elem');
                if ($this->selectedValue == $x)
                {
                    $option->addAttribute('checked', 'checked');
                }
                $option->addAttribute('value', $x);
            }
        }

        $this->addFields($child);

        return $child;
    }
}
