<?php
/**
* Captcha.class.php
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
* Implements a Captcha component
* @package Module\HTMLForm\SpecialElement
*/
class Captcha extends \Module\HTMLForm\HTMLElement
{
    /**
    * The prefix for the captcha key in the session
    */
    const CAPTCHA_KEY_PREFIX = 'captcha_';
    /**
    * The default length of the generated key
    */
    const CAPTCHA_DEFAULT_LENGTH = 8;

    /**
    * @publicget
    * @publicset
    * @var string Specifies a unique identifier for the captcha location
    */
    protected $id = '';

    /**
    * Creates the rating element
    * @param string A unique identifier for the captcha location
    */
    public final function __construct($id)
    {
        $this->id = $id;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public final function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('captcha');

        $child->source = PUBLIC_ROOT . 'htmlform/captchaimage/generate?src=' . $this->id;

        $this->addFields($child);

        return $child;
    }

    /**
    * Gets the captcha image based on the generated code.
    * @param string The unique identifier for this captcha
    * @param int The length of the Captcha
    * @return \Module\HTMLForm\SpecialElement\CaptchaImage The captcha image to output
    */
    public static final function getCaptcha($id, $captchaLength = \Module\HTMLForm\SpecialElement\Captcha::CAPTCHA_DEFAULT_LENGTH)
    {
        $code = self::storeValidationCode($id, $captchaLength);

        $captcha = new CaptchaImage($code, PATH_MODULES . 'htmlform/specialelement/fonts/', 20, 30, 25);

        return $captcha;
    }

    /**
    * Gets a new validation code en stores it in the session
    * @param string The unique identifier for this captcha
    * @param int The length of the code
    * @return string The generated code
    */
    private static final function storeValidationCode($id, $length = \Module\HTMLForm\SpecialElement\Captcha::CAPTCHA_DEFAULT_LENGTH)
    {
        $sm = new \System\HTTP\Storage\Session();

        $code = self::generateCode($length);
        $key = self::CAPTCHA_KEY_PREFIX . $id;
        $sm->$key = $code;

        return $code;
    }

    /**
    * Gets the validationcode, based on the unique identifier given.
    * @param string The unique identifier corresponding to this captcha.
    * @return string The generated code from the session
    */
    public static final function getValidationCode($id)
    {
        $sm = new \System\HTTP\Storage\Session();
        $key = self::CAPTCHA_KEY_PREFIX . $id;

        if (!isset($sm->$key))
        {
            throw new \System\Error\Exception\SystemException('The given unique identifier does not exists in the session. No captcha code can be retrieved.');
        }

        return $sm->$key;
    }

    /**
    * Generates the actual captcha code to use.
    * @param int The length of the code.
    * @return string The code to use
    */
    private static final function generateCode($length = \Module\HTMLForm\SpecialElement\Captcha::CAPTCHA_DEFAULT_LENGTH)
    {
        $allowableCharacters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                                     '2', '3', '4', '5', '6', '7', '8');

        $code = "";
        $charCountMax = count($allowableCharacters) - 1;
        for ($i = 0; $i < $length; $i++)
        {
            $code .= $allowableCharacters[mt_rand(0, $charCountMax)];
        }

        return $code;
    }
}
