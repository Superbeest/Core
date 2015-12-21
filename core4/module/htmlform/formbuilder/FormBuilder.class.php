<?php
/**
* FormBuilder.class.php
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


namespace Module\HTMLForm\FormBuilder;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Wraps commonly used HTML elements for use in a more intuitive user experience.
* @package \Module\HTMLForm\FormBuilder
*/
class FormBuilder extends \System\Base\StaticBase
{
    /**
    * Validates a resultset map to be used with a default structure. input, missing and errors entries are added and map to Validate results
    * for easy service handling. The map is passed by reference.
    * @param \System\Collection\Map The resultset or null to create a new Map
    */
    private static final function validateResultset(\System\Collection\Map &$resultSet = null)
    {
        if ($resultSet == null)
        {
            $resultSet = new \System\Collection\Map();
        }

        if (!isset($resultSet->missing))
        {
            $resultSet->missing = new \System\Collection\Vector();
        }
        if (!isset($resultSet->errors))
        {
            $resultSet->errors = new \System\Collection\Map();
        }
        if (!isset($resultSet->input))
        {
            $resultSet->input = new \System\Collection\Map();
        }
    }

    /**
    * Create a new select box field in an elementcontainer, with room for an error/notice and a label
    * The values for the error/notice and the value are based on the given serviceresult map. If no map is given, empty values will be outputted.
    * Also the previous selected value will be selected by default
    * @see createSelectBox() for manual use.
    * @param mixed The id and the name of the formfield
    * @param \System\Collection\Map the options to be displayed
    * @param \System\Collection\Map The serviceResult to get the error/notice info from
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @param string The default value of the selectbox, if no result has been set
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form.
    */
    public static final function createSelectBoxWithResultSet($id, \System\Collection\Map $options, \System\Collection\Map $serviceResult = null, $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE, $defaultValue = '')
    {
        self::validateResultset($serviceResult);

        return self::createSelectBox($id, $options, self::getFieldValue($id, $defaultValue, $serviceResult->input), self::getErrorNotice($id, $serviceResult->errors, $serviceResult->missing), $label, $validationMethod);
    }

    /**
    * Creates a new selectbox in an elementcontainer, with room for an error/notice block and a label.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @param string The id and the name of the formfield
    * @param \System\Collection\Map the options to be displayed
    * @param string The selectedvalue of the selectbox
    * @param string The error/notice to display in the elementcontainer
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form
    */
    public static final function createSelectBox($id, \System\Collection\Map $options, $value = '', $notice = '', $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE)
    {
        $vectorOptions = new \System\Collection\Vector();

        foreach ($options as $index=>$option)
        {
            $vOption = new \Module\HTMLForm\Element\Option($option, $index);
            $vectorOptions->add($vOption);
        }

        $selectBox = new \Module\HTMLForm\Element\Select($id);
        $selectBox->setId($id);
        $selectBox->addOptions($vectorOptions);
        $selectBox->setSelectedValue($value);

        if (!empty($notice))
        {
        	$selectBox->setClass('notice');
		}

        self::applyValidationScript($selectBox, $validationMethod, '');
        $container = new \Module\HTMLForm\Element\ElementContainer($selectBox, $notice);
        $label = new \Module\HTMLForm\Element\Label($container, $label);

        return $label;
    }

    /**
    * Creates a new input type="password" field in an elementcontainer, with room for an error/notice block and a label.
    * The values for the error/notice and the value are based on the given serviceresult map. If no map is geven, empty values will be outputted.
    * Also the previous entered value will be outputted, if -and only if- this value passes validation.
    * @param string The id and the name of the formfield
    * @param \System\Collection\Map The serviceResult to get the error/notice info from.
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @param string The default value of the field, if no result has been set
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form.
    */
	public static final function createInputPasswordWithResultSet($id, \System\Collection\Map $serviceResult = null, $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE, $defaultValue = '')
	{
		self::validateResultset($serviceResult);

		return self::createInputPassword($id, self::getFieldValue($id, $defaultValue, $serviceResult->input), self::getErrorNotice($id, $serviceResult->errors, $serviceResult->missing), $label, $validationMethod);
	}

	/**
	* Creates a new input type="password" field in an elementcontainer, with room for an error/notice block and a label.
    * Clientside JS validation will be applied and can be chained using bitmasking.
	* @param string The id and the name of the formfield
    * @param string The value to display in the field
    * @param string The error/notice to display in the elementcontainer
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form
	*/
	public static final function createInputPassword($id, $value = '', $notice = '', $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE)
	{
		$inputPassword = new \Module\HTMLForm\Element\InputPassword($id, $value, $id);

		if (!empty($notice))
		{
			$inputText->setClass('notice');
		}

		self::applyValidationScript($inputPassword, $validationMethod, 'blur change');
		$container = new \Module\HTMLForm\Element\ElementContainer($inputPassword, $notice);
		$label = new \Module\HTMLForm\Element\Label($container, $label);

		return $label;
	}

    /**
    * Creates a new input type="text" field in an elementcontainer, with room for an error/notice block and a label.
    * The values for the error/notice and the value are based on the given serviceresult map. If no map is given, empty values will be outputted.
    * Also the previous entered value will be outputted, if -and only if- this value passes validation.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @see createInputText() for manual use.
    * @param string The id and the name of the formfield
    * @param \System\Collection\Map The serviceResult to get the error/notice info from.
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @param string The default value of the field, if no result has been set
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form.
    */
    public static final function createInputTextWithResultSet($id, \System\Collection\Map $serviceResult = null, $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE, $defaultValue = '')
    {
        self::validateResultSet($serviceResult);

        return self::createInputText($id, self::getFieldValue($id, $defaultValue, $serviceResult->input), self::getErrorNotice($id, $serviceResult->errors, $serviceResult->missing), $label, $validationMethod);
    }

	 /**
    * Creates a new input type based on the <$element> objecttype field in an elementcontainer, with room for an error/notice block and a label.
    * The values for the error/notice and the value are based on the given serviceresult map. If no map is given, empty values will be outputted.
    * Also the previous entered value will be outputted, if -and only if- this value passes validation.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @see createInputText() for manual use.
    * @param string The name of the \Module\HTMLForm\Element\Input* class
    * @param string The id and the name of the formfield
    * @param \System\Collection\Map The serviceResult to get the error/notice info from.
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @param string The default value of the field, if no result has been set
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form.
    */
    public static final function createInputElementWithResultSet($element, $id, \System\Collection\Map $serviceResult = null, $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE, $defaultValue = '')
    {
    	self::validateResultset($serviceResult);

    	return self::createInputElement($element, $id, self::getFieldValue($id, $defaultValue, $serviceResult->input), self::getErrorNotice($id, $serviceResult->errors, $serviceResult->missing), $label, $validationMethod);
	}

	/**
    * Creates a new input type based on the <$element> objecttype field in an elementcontainer, with room for an error/notice block and a label.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @param string The name of the \Module\HTMLForm\Element\Input* class
    * @param string The id and the name of the formfield
    * @param string The value to display in the field
    * @param string The error/notice to display in the elementcontainer
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form, or false on failure
    */
    public static final function createInputElement($element, $id, $value = '', $notice = '', $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE)
    {
		if (is_subclass_of($element, '\Module\HTMLForm\Element\Input'))
		{
	        $inputText = new $element($id, $value, $id);

	        if (!empty($notice))
	        {
        		$inputText->setClass('notice');
			}

	        self::applyValidationScript($inputText, $validationMethod, 'blur change');
	        $container = new \Module\HTMLForm\Element\ElementContainer($inputText, $notice);
	        $label = new \Module\HTMLForm\Element\Label($container, $label);

	        return $label;
		}

		return false;
    }

    /**
    * Creates a new input type="text" field in an elementcontainer, with room for an error/notice block and a label.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @param string The id and the name of the formfield
    * @param string The value to display in the field
    * @param string The error/notice to display in the elementcontainer
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form
    */
    public static final function createInputText($id, $value = '', $notice = '', $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE)
    {
        return self::createInputElement('\Module\HTMLForm\Element\InputText', $id, $value, $notice, $label, $validationMethod);
    }

    /**
    * Creates a new textarea field in an elementcontainer, with room for an error/notice block and a label.
    * The values for the error/notice and the value are based on the given serviceresult map. If no map is given, empty values will be outputted.
    * Also the previous entered value will be outputted, if -and only if- this value passes validation.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @see createTextArea() for manual use.
    * @param string The id and the name of the formfield
    * @param \System\Collection\Map The serviceResult to get the error/notice info from.
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @param string The default value of the field, if no result has been set
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form.
    */
    public static final function createTextAreaWithResultSet($id, \System\Collection\Map $serviceResult = null, $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE, $defaultValue = '')
    {
        self::validateResultSet($serviceResult);

        return self::createTextArea($id, self::getFieldValue($id, $defaultValue, $serviceResult->input), self::getErrorNotice($id, $serviceResult->errors, $serviceResult->missing), $label, $validationMethod);
    }

    /**
    * Creates a new textarea field in an elementcontainer, with room for an error/notice block and a label.
    * Clientside JS validation will be applied and can be chained using bitmasking.
    * @param string The id and the name of the formfield
    * @param string The value to display in the field
    * @param string The error/notice to display in the elementcontainer
    * @param string The label to show before the element
    * @param int A bitwise combination of \Module\HTMLForm\FormBuilder\ValidationOptions for JS clientside validation
    * @return \Module\HTMLForm\Element\Label A Label with nested elements to be outputted to a form
    */
    public static final function createTextArea($id, $value = '', $notice = '', $label = '', $validationMethod = \Module\HTMLForm\FormBuilder\ValidationOptions::VALIDATE_NONE)
    {
        $textArea = new \Module\HTMLForm\Element\TextArea($id, $value, $id);

		if (!empty($notice))
        {
        	$textArea->setClass('notice');
		}

        self::applyValidationScript($textArea, $validationMethod, 'blur change keypress');
        $container = new \Module\HTMLForm\Element\ElementContainer($textArea, $notice);
        $label = new \Module\HTMLForm\Element\Label($container, $label);

        return $label;
    }

    /**
    * Creates a new submit button with a label. The properties of the submit button are default 'submit'. No label text is used.
    * @param string The text to show on the button
    * @return \Module\HTMLForm\Element\Label The Label containing the submit button.
    */
    public static final function createSubmitButton($caption)
    {
        $submitButton = new \Module\HTMLForm\Element\ButtonSubmit('submit');
        $submitButton->setValue($caption);
        $submitButton->setId('submit');
        $labelSubmitButton =  new \Module\HTMLForm\Element\Label($submitButton, '');

        return $labelSubmitButton;
    }

    /**
    * Gets the field value from the given inputmap.
    * We check if the field exists in the map and then give the output if it is not empty, otherwise the defaultValue is returned.
    * @param string The name of the field to check the value of.
    * @param string The default value to show in case of no valid input field
    * @param \System\Collection\Map The map containing the posted input fields. This usually is the result of getFiltered() from the Validate class.
    */
    public static final function getFieldValue($formField, $defaultValue, \System\Collection\Map $inputMap)
    {
        if (isset($inputMap->$formField))
        {
            $val = $inputMap->$formField;
            if (!empty($val))
            {
                return $inputMap->$formField;
            }
        }

        return $defaultValue;
    }

    /**
    * Gets the error/notice value from the errormap and the missingvector.
    * First we check if the given formField occurs in the errormap, if not the missingvector is queried.
    * If nothing is found an empty string is returned.
    * This function is language independant using the 'form_notpresent', 'form_invalidrange', 'form_invalidvalue' definitions
    * @param string The field to look for.
    * @param \System\Collection\Map The Errormap
    * @param \System\Collection\Vector The missing field vector
    */
    public static final function getErrorNotice($formField, \System\Collection\Map $errorMap, \System\Collection\Vector $missingFields)
    {
        $errorNotice = '';

        switch (true)
        {
            case $missingFields->contains($formField):
                $errorNotice = \System\Internationalization\Language::getSentence('form_notpresent');
                break;
            case ((isset($errorMap->$formField)) &&
                  ($errorMap->$formField == \System\Security\ValidateResult::VALIDATE_RANGE)):
                $errorNotice = \System\Internationalization\Language::getSentence('form_invalidrange');
                break;
            case ((isset($errorMap->$formField)) &&
                  ($errorMap->$formField == \System\Security\ValidateResult::VALIDATE_INVALIDVALUE)):
            case (isset($errorMap->$formField)):
                $errorNotice = \System\Internationalization\Language::getSentence('form_invalidvalue');
                break;
            default:
                //we ignore this case and output no error
        }

        return $errorNotice;
    }

    /**
    * Applies the validation script to an onInit attribute of an element. The combining of the scripts gets done by applying some standard bitmasking.
    * This function needs to be expanded upon the creation of new validation function.
    * @param \Module\HTMLForm\HTMLInputFormElement The element to apply the onInit to
    * @param int The validation bitmask to apply
    * @param string The events to bind to. These are JQuery events.
    */
    private static final function applyValidationScript(\Module\HTMLForm\HTMLInputFormElement $element, $validationMethod, $events)
    {
        if ($validationMethod == ValidationOptions::VALIDATE_NONE)
        {
            return;
        }

        $script = '';
        $functionCalls = array();

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_EMAIL))
        {
            $script .= ValidationJS::JS_EMAIL;
            $functionCalls[] = '(' . ValidationJS::JS_EMAIL_CALL . '($(this)))';
        }

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_NOTEMPTY))
        {
            $script .= ValidationJS::JS_NOTEMPTY;
            $functionCalls[] = '(' . ValidationJS::JS_NOTEMPTY_CALL . '($(this)))';
        }

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_PHONE))
        {
            $script .= ValidationJS::JS_PHONE;
            $functionCalls[] = '(' . ValidationJS::JS_PHONE_CALL . '($(this)))';
        }

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_ZIP_NL))
        {
            $script .= ValidationJS::JS_ZIP_NL;
            $functionCalls[] = '(' . ValidationJS::JS_ZIP_NL_CALL . '($(this)))';
        }

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_NUMBERS))
        {
            $script .= ValidationJS::JS_NUMBERS;
            $functionCalls[] = '(' . ValidationJS::JS_NUMBERS_CALL . '($(this)))';
        }

        if (ValidationOptions::contains($validationMethod, ValidationOptions::VALIDATE_URL))
        {
            $script .= ValidationJS::JS_URL;
            $functionCalls[] = '(' . ValidationJS::JS_URL_CALL . '($(this)))';
        }

        $script = self::prepareValidationJS($element, $script, $functionCalls, $events);
        $element->setOnInit($script);
    }

    /**
    * Outputs the initialization script for the validation and replaces the predefined js template stuff for the actual fieldnames and values.
    * @param \Module\HTMLForm\HTMLInputFormElement The element to apply onInit to.
    * @param string The script to apply. This is the sum of all the functions.
    * @param array An array containing all the function calls.
    * @param string The events to bind to.
    */
    public static final function prepareValidationJS(\Module\HTMLForm\HTMLInputFormElement $element, $validationScript, array $functionCalls, $events)
    {
        $validationScript = '
            $(function() {
                $("#{ID}").on("' . $events . '", function() {
                    if ({FUNCTIONS}) {
                        $(this).removeClass("error notice");
                    }
                    else {
                        $(this).addClass("error");
                    }
                });
            });'
            . $validationScript;

        $validationScript = str_ireplace('{FUNCTIONS}', implode('&&', $functionCalls), $validationScript);
        $validationScript = str_ireplace('{ID}', $element->getId(), $validationScript);

        return $validationScript;
    }
}
