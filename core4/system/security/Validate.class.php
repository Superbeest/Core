<?php
/**
* Validate.class.php
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


namespace System\Security;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* System to validate the contents of given variables
* @package \System\Security
*/
class Validate extends \System\Base\Base
{
    /**
    * @var \System\Collection\Vector These variables are required
    */
    protected $requiredVariables = null;
    /**
    * @var \System\Collection\Vector These variables are optional
    */
    protected $optionalVariables = null;

    /**
    * @var \System\Collection\Vector All the filterarguments are added here
    */
    protected $filterArguments = null;

    /**
    * @var \System\Collection\Vector Contains the missing fields
    */
    protected $missing = null;
    /**
    * @var \System\Collection\Map Contains all the errors that occured while validating
    */
    protected $errors = null;
    /**
    * @var \System\Collection\Map Contains all the filtered values
    */
    protected $filtered = null;

    /**
    * Creates the Filter object to sanitize
    */
    public final function __construct()
    {
        $this->filterArguments = new \System\Collection\Vector();

        $this->missing = new \System\Collection\Vector();
        $this->errors = new \System\Collection\Map();
        $this->filtered = new \System\Collection\Map();
    }

    /**
    * Checks if there are any duplicate filters. This is implemented by checking if the same fieldname is already in the list of
    * registered filters.
    * @param string The name of the field to check for
    */
    private final function checkDuplicateFilter($fieldName)
    {
        if ($this->filterArguments->contains($fieldName))
        {
            throw new \System\Error\Exception\DuplicateFilterException($fieldName . ' was already set in this filter');
        }
        $this->filterArguments[] = $fieldName;
    }

    /**
    * Checks if the given value is set and thus present. It only triggers when the required variable is true
    * It also adds the field to the missing list if the value is not set.
    * @param mixed The value to test for null
    * @param string The name of the field
    * @param boolean If the field is required or not
    * @return boolean The result of the null check
    */
    private final function checkRequired($value, $fieldName, $required)
    {
        //check if the value is not set and if it is required
        if (($value === null) &&
            ($required))
        {
            $this->missing[] = $fieldName;
            return false;
        }
        return true;
    }

    /**
    * Does the actual filtering. It checks if the given filter, combined with the flags and options match against the given value.
    * @param mixed The value to test
    * @param string The name of the field
    * @param integer The filter to apply
    * @param array The optional arguments to present to the filter
    * @return integer The result of the filtercheck
    */
    private final function filterVariable($value, $fieldName, $filter, $required, array $arguments = array())
    {
        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        $returnValue = filter_var($value, $filter, $arguments);
        if ($returnValue === false)
        {
            $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
            return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        }

        //add the filtered data to the filtered set
        $this->filtered[$fieldName] = $returnValue;

        return \System\Security\ValidateResult::VALIDATE_OK;
    }

	/**
	* Returns if the given fieldname is in the filtered variables
	* @param string The fieldname to check
	* @return bool true on filtered, false otherwise
	*/
    public final function isFieldFiltered($fieldName)
    {
    	return $this->getFiltered()->keyExists($fieldName);
	}

    /**
    * Checks if the given field corresponds to the given matchValue. This can be optionally caseinsensitive or casesensitive
    * @param mixed The value to check
    * @param string The name of the field
    * @param string The value to match against
    * @param boolean When true the value must match cases, otherwise false
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isValue($value, $fieldName, $matchValue, $caseSensitive = false, $required = false)
    {
        return $this->matchRegex($value, $fieldName, '/^' . $matchValue . '$/' . (!$caseSensitive ? 'i' : ''), $required);
    }

    /**
    * Checks if the given value exists in the range of the given $matchValues. This is done by strict matching each entry.
    * @param mixed The value to check
    * @param string The name of the field
    * @param \System\Collection\Vector The values to match against.
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function inValueRange($value, $fieldName, \System\Collection\Vector $matchValues, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);

        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        if ($matchValues->contains($value, true))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
    }

	/**
	* Checks if the given value exists in the given struct. This is done by losely typed matching each entry.
	* @param mixed The value to check
    * @param string The name of the field
	* @param \System\Base\BaseStruct The struct to check
	* @param boolean Whether or not the value must be set
	* @return integer The result of the filter
	*/
    public final function inStruct($value, $fieldName, \System\Base\BaseStruct $struct, $required = false)
    {
    	$this->checkDuplicateFilter($fieldName);

        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        $found = false;
        foreach ($struct as $structValue)
        {
        	if ($value == $structValue)
        	{
        		$found = true;
        		break;
			}
		}

		if ($found)
		{
			//add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
		}

		$this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
	}

    /**
    * Checks if the given value is considered an integer and optionally checks the ranges of the value.
    * @param mixed The value to check
    * @param string The name of the field
    * @param integer The minimum value of the integer
    * @param integer The maximum value of the integer
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isInt($value, $fieldName, $minimumValue = null, $maximumValue = null, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_INT;

        $arguments = array('options' => array());
        if ((is_int($minimumValue)) ||
            (ctype_digit($minimumValue)))
        {
            $arguments['options']['min_range'] = $minimumValue;
        }
        if ((is_int($maximumValue)) ||
            (ctype_digit($maximumValue)))
        {
            $arguments['options']['max_range'] = $maximumValue;
        }
        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value is a float, with configurable decimal point and a thousand separator.
    * @param mixed The value to check
    * @param string The name of the field
    * @param string The value used as a decimal point. Only . or , can be used.
    * @param boolean Whether or not to allow a thousand separator
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isFloat($value, $fieldName, $decimalPoint = '.', $allowThousandSeparator = true, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if (($decimalPoint != '.') &&
            ($decimalPoint != ','))
        {
            throw new \InvalidArgumentException("the decimalPoint variable is not a comma nor a point value");
        }

        $filter = FILTER_VALIDATE_FLOAT;

        $arguments = array('options' => array());
        $arguments['options']['decimal'] = $decimalPoint;

        if ($allowThousandSeparator)
        {
            $arguments['options']['flags'] = FILTER_FLAG_ALLOW_THOUSAND;
        }

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value is numeric
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not to allow decimal fractions
    * @param string The value used as a decimal point. Only . or , can be used.
    * @param boolean Whether or not to allow a thousand separator
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isNumeric($value, $fieldName, $allowDecimalFractions = true, $decimalPoint = '.', $allowThousandSeparator = true, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if (($decimalPoint != '.') &&
            ($decimalPoint != ','))
        {
            throw new \InvalidArgumentException("the decimalPoint variable is not a comma nor a point value");
        }

        $filter = FILTER_VALIDATE_FLOAT;
        $arguments = array('options' => array(), 'flags' => FILTER_FLAG_NONE);
        $arguments['options']['decimal'] = $decimalPoint;
        if ($allowThousandSeparator)
        {
            $arguments['flags'] |= FILTER_FLAG_ALLOW_THOUSAND;
        }
        if ($allowDecimalFractions)
        {
            $arguments['flags'] |= FILTER_FLAG_ALLOW_FRACTION;
        }

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value is an email address.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isEmail($value, $fieldName, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_EMAIL;

        return $this->filterVariable($value, $fieldName, $filter, $required);
    }

    /**
    * Checks if the given value is a fully qualified URL. This includes a url scheme and a path.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not a query string is required
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isFullURL($value, $fieldName, $queryStringRequired = false, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_URL;

        $arguments = array('flags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED);

        if ($queryStringRequired)
        {
            $arguments['flags'] |= FILTER_FLAG_QUERY_REQUIRED;
        }

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value is an URL.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not a query string is required
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isURL($value, $fieldName, $queryStringRequired = false, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_URL;

        $arguments = array('flags' => FILTER_FLAG_HOST_REQUIRED);

        if ($queryStringRequired)
        {
            $arguments['flags'] |= FILTER_FLAG_QUERY_REQUIRED;
        }

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

	/**
	* Checks if the given value is equal to the given matchValue.
	* @param mixed The value to check
    * @param string The name of the field
    * @param string The value to match against
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
	*/
    public final function isEqual($value, $fieldName, $matchValue, $required = false)
    {
    	$this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        if ($value == $matchValue)
        {
        	//add the filtered data to the filtered set
	        $this->filtered[$fieldName] = $value;

	        return \System\Security\ValidateResult::VALIDATE_OK;
		}

		$this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
	}

    /**
    * Checks if the given value is a boolean.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isBool($value, $fieldName, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

		//the filter does not support actual booleans
        if (is_bool($value))
        {
        	$value = $value ? 'true' : 'false';
		}

		//this filter returns null instead of false, so we need to implement it ourselves
		$arguments = array('flags' => FILTER_NULL_ON_FAILURE);

        $returnValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, $arguments);
        if ($returnValue === null)
        {
            $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
            return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        }

        //add the filtered data to the filtered set
        $this->filtered[$fieldName] = (bool)$returnValue;

        return \System\Security\ValidateResult::VALIDATE_OK;
    }

    /**
    * Checks if the given value is an empty string.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isEmptyString($value, $fieldName, $required = false)
    {
        return $this->checkTextLength($value, $fieldName, 0, 0, $required);
    }

    /**
    * Checks if the given value is a positive integer
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isPositiveInt($value, $fieldName, $required = false)
    {
        return $this->isInt($value, $fieldName, 0, null, $required);
    }

    /**
    * Validates the given input and checks if it is a valid IP address. IPv6 is supported if PHP is compiled with IPv6 support.
    * @param mixed The value to check
    * @param string The name of the field
    * @param bool Whether or not the value must be set
    * @param bool True to enable support for IPv4
    * @param bool True to enable support for IPv6
    * @param bool Do not allow Reserved IP ranges. Fails on 0.0.0.0/8, 169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4, not applicable for IPv6
    * @param bool Do not allow private IP ranges. Fails on 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16 for IPv4, and prefix of FD or FC for IPv6
    * @return integer The result of the filter
    */
    public final function isIPAddress($value, $fieldName, $required = false, $allowIPv4 = true, $allowIPv6 = false, $disallowReservedRange = true, $disallowPrivateRange = true)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_IP;

        $arguments = array('flags' => 0);

        if ($allowIPv4)
        {
            $arguments['flags'] |= FILTER_FLAG_IPV4;
        }

        if ($allowIPv6)
        {
            $arguments['flags'] |= FILTER_FLAG_IPV6;
        }

        if ($disallowReservedRange)
        {
            $arguments['flags'] |= FILTER_FLAG_NO_RES_RANGE;
        }

        if ($disallowPrivateRange)
        {
            $arguments['flags'] |= FILTER_FLAG_NO_PRIV_RANGE;
        }

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value matches agains the given PCRE regex.
    * @param mixed The value to check
    * @param string The name of the field
    * @param string A PCRE compatible regex to match against
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function matchRegex($value, $fieldName, $regex, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        $filter = FILTER_VALIDATE_REGEXP;

        $arguments = array('options' => array());
        $arguments['options']['regexp'] = $regex;

        return $this->filterVariable($value, $fieldName, $filter, $required, $arguments);
    }

    /**
    * Checks if the given value matches agains the given PCRE regex.
    * Places the match values in the filtered result in the form of an array.
    * @param mixed The value to check
    * @param string The name of the field
    * @param string A PCRE compatible regex to match against
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function matchRegexFilter($value, $fieldName, $regex, $required = false)
    {
    	$returnValue = $this->matchRegex($value, $fieldName, $regex, $required);
		if ($returnValue == ValidateResult::VALIDATE_OK)
		{
			$matches = array();
			preg_match($regex, $value, $matches);
			$this->filtered[$fieldName] = $matches;
		}

		return $returnValue;
	}

    /**
    * Checks if the given value is a string with a length between the given boundaries.
    * @param mixed The value to check
    * @param string The name of the field
    * @param int The minimum amount of characters, default null
    * @param int The maximum amount of characters, default null
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function checkTextLength($value, $fieldName, $minimumChars = null, $maximumChars = null, $required = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        //check the type
        if (!is_string($value))
        {
            $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
            return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        }

        //we trim the string before working with it
        $value = trim($value);

        $length = mb_strlen($value);

        if ($minimumChars !== null)
        {
            if ((!ctype_digit($minimumChars)) &&
                (!is_int($minimumChars)))
            {
                throw new \InvalidArgumentException('given arguments are not valid. Please specify proper minimum and maximum values');
            }
            if ($length < $minimumChars)
            {
                $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_RANGE;
                return \System\Security\ValidateResult::VALIDATE_RANGE;
            }
        }

        if ($maximumChars !== null)
        {
            if ((!ctype_digit($maximumChars)) &&
                (!is_int($minimumChars)))
            {
                throw new \InvalidArgumentException('given arguments are not valid. Please specify proper minimum and maximum values');
            }
            if ($length > $maximumChars)
            {
                $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_RANGE;
                return \System\Security\ValidateResult::VALIDATE_RANGE;
            }
        }

        //add the filtered data to the filtered set
        $this->filtered[$fieldName] = (string)$value;

        return \System\Security\ValidateResult::VALIDATE_OK;
    }

    /**
    * Checks to see if the given field is a hex color field. Supports fields ranging from #000000 to #FFFFFF.
    * The first sign may be a '#', or may be omitted.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @return integer The result of the filter
    */
    public final function isHexColor($value, $fieldName, $required = false)
    {
        return $this->matchRegex($value, $fieldName, '/^#?([a-f0-9]){3}(([a-f0-9]){3})?$/i', $required);
    }

    /**
    * Checks if the given value is a date following the following format: "2013-11-07T00:00:00+01:00" or "2013-11-07T00:00:00"
    * When createDate is true and the value is not send and required is true, then the filtered result will not be created as a timestamp
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @param bool True to create a \System\Calendar\Time object out of a filtered value, false to leave it a string
    * @return integer The result of the filter
    */
    public final function isDatetime($value, $fieldName, $required = false, $createDate = false)
    {
		$returnVal = $this->matchRegex($value, $fieldName, '/^([1-9][0-9]{3})-([0-1][0-9])-([0-3][0-9])T([0-2][0-9]):([0-5][0-9]):([0-5][0-9])((\+|-)([0-2][0-9]):([0-5][0-9]))?$/i', $required);

		if (($createDate) &&
			($returnVal == \System\Security\ValidateResult::VALIDATE_OK))
		{
			if (!((empty($value)) && (!$required)))
        	{
        		$this->filtered[$fieldName] = new \System\Calendar\Time(strtotime((string)$value));
			}
		}

        return $returnVal;
	}

    /**
    * Checks of the given value is a date. A few formats are supported.
    * We support 'DD-MM-YYYY', 'DD-MM-YYYYT00:00:00', 'YYYY-MM-DD', 'YYYY-MM-DDT00:00:00', 'YY(YY)-MM-DD H:i:s'. Also checks if the date is a valid date.
    * @param mixed The value to check
    * @param string The name of the field
    * @param boolean Whether or not the value must be set
    * @param bool True to create a \System\Calendar\Time object out of a filtered value, false to leave it a string
    * @return integer The result of the filter
    */
    public final function isDate($value, $fieldName, $required = false, $createDate = false)
    {
        $this->checkDuplicateFilter($fieldName);
        if (!$this->checkRequired($value, $fieldName, $required))
        {
            return \System\Security\ValidateResult::VALIDATE_NOTPRESENT;
        }

        if ((empty($value)) &&
            (!$required))
        {
            //add the filtered data to the filtered set
            $this->filtered[$fieldName] = $value;

            return \System\Security\ValidateResult::VALIDATE_OK;
        }

        //check the type
        if (!is_string($value))
        {
            $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
            return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        }

		//remove time
		$tPos = mb_stripos($value, 't');
		if ($tPos !== false)
		{
			$value = substr($value, 0, $tPos);
		}

        //we trim the string before working with it
        $value = trim($value);

        switch (true)
        {
            case preg_match('/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/', $value) == 1:
                list($day, $month, $year) = explode('-', $value);
                if (!checkdate($month, $day, $year))
                {
                    $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                    return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                }
                break;
            case preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value) == 1:
                list($year, $month, $day) = explode('-', $value);
                if (!checkdate($month, $day, $year))
                {
                    $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                    return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                }
                break;
            case preg_match('/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?: ([0-2][0-9]):([0-5][0-9])(:([0-5][0-9]))?)?$/', $value) == 1:
                $time = date('Y-m-d', strtotime($value));
                list($year, $month, $day) = explode('-', $time);
                if (!checkdate($month, $day, $year))
                {
                    $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                    return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                }
                break;
            default:
                $this->errors[$fieldName] = \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
                return \System\Security\ValidateResult::VALIDATE_INVALIDVALUE;
        }

        //add the filtered data to the filtered set
        if ($createDate)
        {
        	$this->filtered[$fieldName] = new \System\Calendar\Time(strtotime((string)$value));
		}
		else
		{
        	$this->filtered[$fieldName] = (string)$value;
		}

        return \System\Security\ValidateResult::VALIDATE_OK;
    }

    /**
    * Returns all the missing fields from the given input. The missing fields are only the fields marked as required.
    * @return \System\Collection\Vector A Vector containing the missing fields
    */
    public final function getMissingFields()
    {
        return $this->missing;
    }

    /**
    * Returns all the errorneous fields and their corresponding errors.
    * @return \System\Collection\Map A map containing the errors
    */
    public final function getErrors()
    {
        return $this->errors;
    }

    /**
    * Returns all the filtered values from the given input set.
    */
    public final function getFiltered()
    {
        return $this->filtered;
    }

    /**
    * Returns whether or not the given input variables passed the given tests.
    * It checks for missing fields and validation errors
    * @return boolean Returns whether or not the given input variables are ok.
    */
    public final function isInputOk()
    {
        return (($this->getErrors()->count() == 0) &&
                ($this->getMissingFields()->count() == 0));
    }
}
