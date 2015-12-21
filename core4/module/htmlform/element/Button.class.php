<?php
/**
* Button.class.php
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
* Basic element button implementation
* @package \Module\HTMLForm\Element
*/
abstract class Button extends \Module\HTMLForm\HTMLInputFormElement
{
    /**
    * Disabled/Enabled switch constant
    */
    const DISABLED_ENABLED      = '';
    /**
    * Disabled/Enabled switch constant
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
    * @var string Script to be run when an element gets focus
    */
    protected $onFocus = '';

    /**
    * Creates the object.
    * @param string The name of the button
    */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    */
    protected function addFields(\SimpleXMLElement $xml)
    {
        parent::addFields($xml);

        $xml->disabled = $this->disabled;
        $xml->name = $this->name;
        $xml->value = $this->value;

        $xml->events->onblur = $this->onBlur;
        $xml->events->onfocus = $this->onFocus;
    }
}
