<?php
/**
* Captchaimage.ctrl.php
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
* The controller that generates the actual validation image.
* @package \Module\HTMLForm
*/
final class Captchaimage extends \System\Web\Controller
{
    /**
    * This function is called in the beginning of the controller, before the execution of the specified
    * requesthandler (for example: defaultAction()). It is used to register request services that
    * can process the users input.
    * All the given services will be processed once each call, allowing for successive service calls.
    * @param \System\Collection\Vector The vector with all the services to call.
    */
    protected function deployServices(\System\Collection\Vector $services)
    {
    }

    public function defaultAction()
    {
        $this->generate();
    }

    /**
    * Generates the captcha image. It is based on the src get parameter in the html.
    * This src parameter is used to uniquely identify the captcha.
    */
    public function generate()
    {
        $get = new \System\HTTP\Request\Get();

        $captchaLength = SpecialElement\Captcha::CAPTCHA_DEFAULT_LENGTH;

        if (defined('CAPTCHA_DEFAULT_LENGTH'))
        {
            $captchaLength = constant('CAPTCHA_DEFAULT_LENGTH');
        }

        $image = SpecialElement\Captcha::getCaptcha($get->src, $captchaLength);

        $renderer = new \System\Output\Renderer\JPEGRenderer();
        $renderer->render($image, 75);
        $surface = \System\Output\RenderSurface::getSurface('\System\Output\BufferSurface');
        $surface->setRenderer($renderer);
        $this->setRenderSurface($surface);
    }
}