<?php
/**
* InputImage.class.php
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
* The image type input element
* @package \Module\HTMLForm\Element
*/
class InputImage extends \Module\HTMLForm\Element\InputSubmit
{
    /**
    * @publicget
    * @publicset
    * @var bool Specifies an alternate text for an image input (only for type="image")
    */
    protected $alt = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the URL to an image to display as a submit button (only for type="image")
    */
    protected $src = '';

    /**
    * @publicget
    * @publicset
    * @var int The height and width attributes specify the height and width of an <input> element.
    */
    protected $height = '';

    /**
    * @publicget
    * @publicset
    * @var int The height and width attributes specify the height and width of an <input> element.
    */
    protected $width = '';

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = parent::generateXML($xml);

        $child->alt = $this->alt;
        $child->src = $this->src;

        $child->height = $this->height;
        $child->width = $this->width;

        return $child;
    }

    /**
	* Outputs the current type to the xml. Override this function to implement
	* your own type
	* @param \SimpleXMLElement The xml tree to append to
	*/
    protected function outputType(\SimpleXMLElement $xml)
    {
    	$xml->addAttribute('type', 'image');
	}
}
