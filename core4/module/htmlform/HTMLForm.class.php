<?php
/**
* HTMLForm.class.php
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


namespace Module\HTMLForm;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* The encapsulation of an HTML Form element. This class provides functionality to represent a form.
* @package \Module\HTMLForm
*/
class HTMLForm extends \Module\HTMLForm\HTMLFormElement
{
    /**
    * Application enctype. This is used to encode the contents for sending.
    */
    const ENCTYPE_APPLICATION = 'application/x-www-form-urlencoded';
    /**
    * Indicates the posted data is multipart. This is used for files, etc.
    */
    const ENCTYPE_MULTIPART = 'multipart/form-data';
    /**
    * Default form enctype. The data is plainly send.
    */
    const ENCTYPE_TEXT = 'text/plain';

    /**
    * To send the contents of the form by GET
    */
    const METHOD_GET = 'get';
    /**
    * To send the contents of the form by POST
    */
    const METHOD_POST = 'post';

    /**
    * The maximum amount of form tokens available. When more tokens are used per session, the first tokens are invalidated,
    * thus mimicking a queue principle.
    */
    const FORMTOKEN_MAX_AMOUNT = 20;
    /**
    * The fieldname of the token. This will be the name of a hidden field in every form.
    */
    const FORMTOKEN_FIELDNAME = 'token';

    /**
    * @var \System\Collection\Vector A collection with all the elements in the form
    */
    private $elements = null;

    /**
    * @publicget
    * @publicset
    * @var string Specifies where to send the form-data when a form is submitted
    */
    protected $action = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the types of files that can be submitted through a file upload
    */
    protected $accept = '';

    /**
    * @publicget
    * @publicset
    * @var string Specifies the character-sets the server can handle for form-data
    */
    protected $acceptCharset = '';

	/**
	* @publicget
	* @publicset
	* @var bool True to support autocomplete, false to disable
	*/
    protected $autoComplete = true;

	/**
	* @publicget
	* @publicset
	* @var bool When True, it specifies that the form-data (input) should not be validated when submitted
	*/
    protected $noValidate = false;

    /**
    * @publicget
    * @publicset
    * @var string Specifies how form-data should be encoded before sending it to a server
    */
    protected $encType = self::ENCTYPE_APPLICATION;

    /**
    * @publicget
    * @publicset
    * @var bool True to add the token field, false otherwise
    */
    protected $addTokenField = true;

    /**
    * @publicget
    * @publicset
    * @var string Specifies how to send form-data
    */
    protected $method = self::METHOD_POST;

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when a form is submitted
    */
    protected $onSubmit = '';

    /**
    * @publicget
    * @publicset
    * @var string Script to be run when a form is reset
    */
    protected $onReset = '';

	/**
	* @publicget
	* @publicset
	* @var string Sets the target of the form
	*/
    protected $target = '';

    /**
    * Creates a form.
    * @param string The id of the form. This must be a unique value.
    */
    public function __construct($id)
    {
        $this->elements = new \System\Collection\Vector();

        $this->id = $id;
    }

    /**
    * Adds an element to the form
    * @param \Module\HTMLForm\HTMLFormElement The element to add
    */
    public final function addElement(\Module\HTMLForm\HTMLElement $element)
    {
        $this->elements[] = $element;
    }

    /**
    * Adds multiple elements in a vector to the Form.
    * For speed purposes, there is no type checking done. Do note that a form expects \Module\HTMLForm\HTMLFormElement elements.
    * @param \System\Collection\Vector A Vector containing form elements
    */
    public final function addElements(\System\Collection\Vector $elements)
    {
        $this->elements->combine($elements);
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $form = $xml->addChild('form');

        $form->action = $this->action;
        $form->accept = $this->accept;
        $form->acceptcharset = $this->acceptCharset;
        $form->enctype = $this->encType;
        $form->method = $this->method;
        $form->target = $this->target;

        //this needs a different setting
        if (!$this->autoComplete)
        {
        	$form->autocomplete = 'off';
		}

		if ($this->noValidate)
		{
			$form->novalidate = 'on';
		}

        $form->events->onreset = $this->onReset;
        $form->events->onsubmit = $this->onSubmit;

        $elements = $form->addChild('formelements');

        if ($this->getAddTokenField())
        {
            //add the formtoken element as a regular input type="hidden" element
            $hidden = new \Module\HTMLForm\Element\InputHidden(self::FORMTOKEN_FIELDNAME, self::getFormToken($this->id));
            $this->addElement($hidden);
        }

        $initContent = '';

        foreach ($this->elements as $element)
        {
            $element->generateXML($elements);

            $initContent .= $element->getOnInit();
        }

        //add the initcontent to the xml, so all elements get initialized upon form display
        $form->init = $initContent;

        parent::addFields($form);

        return $form;
    }

    /**
    * Generates a new unique form token to prevent XSRF attacks.
    * The token will be attached only to the given form id. It will not be possible to use this key to validate another form.
    * @param string The id of the form.
    * @return string The generated token, 32 characters long.
    */
    public static final function getFormToken($formId)
    {
        $token = md5(uniqid(rand(), true));

        $sm = new \System\HTTP\Storage\Session();

        $tokens = array();
        if ((isset($sm->form_tokens)) &&
            (is_array($sm->form_tokens)))
        {
            $tokens = $sm->form_tokens;
        }

        if (count($tokens) >= self::FORMTOKEN_MAX_AMOUNT)
        {
            array_shift($tokens);
        }

        $tokens[] = array($formId, $token);

        $sm->form_tokens = $tokens;

        return $token;
    }

    /**
    * Checks the validity of the given form token against the registered valid tokens.
    * The values of these tokens are stored in the session.
    * Do note: upon succesfull validation, the token will be invalidated.
    * @param string The id of the form.
    * @param string The token to validate
    * @return bool True on valid token, false otherwise.
    */
    public static final function isValidFormToken($formId, $token)
    {
        $sm = new \System\HTTP\Storage\Session();

        if ((isset($sm->form_tokens)) &&
            (is_array($sm->form_tokens)))
        {
            $tokens = $sm->form_tokens;
            $ok = array_search(array($formId, $token), $tokens);

            if ($ok !== false)
            {
                unset($tokens[$ok]);
                $sm->form_tokens = $tokens;
            }

            return ($ok !== false);
        }

        return false;
    }
}