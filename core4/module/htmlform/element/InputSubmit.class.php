<?php
/**
* InputSubmit.class.php
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
* The submit input type element
* @package \Module\HTMLForm\Element
*/
class InputSubmit extends \Module\HTMLForm\Element\Input
{
	/**
	* @publicget
	* @publicset
	* @var string The formaction attribute specifies the URL of a file that will process the input control when the form is submitted.
	*/
    protected $formAction = '';

    /**
    * @publicset
    * @publicget
    * @var The formenctype attribute specifies how the form-data should be encoded when submitting it to the server (only for forms with method="post")
    */
    protected $formEnctype = '';

    /**
	* @publicget
	* @publicset
	* @var string The formtarget attribute specifies a name or a keyword that indicates where to display the response that is received after submitting the form.
	*/
    protected $formTarget = '';

    /**
	* @publicget
	* @publicset
	* @var string The formmethod attribute defines the HTTP method for sending form-data to the action URL.
	*/
    protected $formMethod = '';

	/**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that the <input> element should not be validated when submitted.
	*/
    protected $formNoValidate = false;

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('input');
        $this->outputType($child);

        $child->formaction = $this->formAction;
        $child->formenctype = $this->formEnctype;
        $child->formtarget = $this->formTarget;
        $child->formmethod = $this->formMethod;

		if ($this->formNoValidate)
		{
        	$child->formnovalidate = $this->formNoValidate;
		}

        $this->addFields($child);

        return $child;
    }

    /**
	* Outputs the current type to the xml. Override this function to implement
	* your own type
	* @param \SimpleXMLElement The xml tree to append to
	*/
    protected function outputType(\SimpleXMLElement $xml)
    {
    	$xml->addAttribute('type', 'submit');
	}
}