<?php
/**
* InputEmail.class.php
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
* Implements the InputEmail element
* @package \Module\HTMLForm\Element
*/
class InputEmail extends \Module\HTMLForm\Element\InputText
{
	/**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that the user is allowed to enter more than one value in the <input> element
	*/
	protected $multiple = false;

	/**
	* Outputs the current type to the xml. Override this function to implement
	* your own type
	* @param \SimpleXMLElement The xml tree to append to
	*/
    protected function outputType(\SimpleXMLElement $xml)
    {
    	$xml->addAttribute('type', 'email');
	}

	/**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
		$child = parent::generateXML($xml);

		$child->multiple = $this->multiple;

		return $child;
    }
}