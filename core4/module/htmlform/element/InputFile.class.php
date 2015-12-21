<?php
/**
* InputFile.class.php
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
* The file input element
* @package \Module\HTMLForm\Element
*/
class InputFile extends \Module\HTMLForm\Element\Input
{
    /**
    * The default filesize in butes for an upload. This is 2Mb
    */
    const DEFAULT_FILESIZE = 2097152;

    /**
    * @publicget
    * @publicset
    * @var int The maximum filesize that can be uploaded in bytes.
    */
    private $fileSize = self::DEFAULT_FILESIZE;

    /**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that the user is allowed to enter more than one value in the <input> element
	*/
	protected $multiple = false;

	/**
	* @publicget
	* @publicset
	* @var bool When true, it specifies that an input field must be filled out before submitting the form.
	*/
    protected $required = false;

    /**
    * @publicget
    * @publicset
    * @var string Specifies the types of files that can be submitted through a file upload (only for type="file") (MIME-Type)
    */
    protected $accept = '';

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        //add the maximum filesize
        $hidden = new \Module\HTMLForm\Element\InputHidden('MAX_FILE_SIZE');
        $hidden->setValue((string)$this->fileSize);
        $hidden->generateXML($xml);

        //we add a APC_UPLOAD_PROGRESS block is the server supporting APC
        //requires 'apc.rfc1867 = on' in php.ini
        if ((function_exists('apc_fetch')) &&
            (ini_get('apc.rfc1867')))
        {
            $hidden = new \Module\HTMLForm\Element\InputHidden('APC_UPLOAD_PROGRESS');
            $hidden->setValue(uniqid()); //this unique id will be used as the key
            $hidden->generateXML($xml);
        }

        $child = $xml->addChild('input');
        $child->addAttribute('type', 'file');

        $child->accept = $this->accept;
        $child->multiple = $this->multiple;
        $child->required = $this->required;

        $this->addFields($child);

        return $child;
    }
}