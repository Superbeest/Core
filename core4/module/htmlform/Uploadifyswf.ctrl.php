<?php
/**
* Uploadifyswf.ctrl.php
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
* Outputs the UploadifySWF file
* @package \Module\HTMLForm
*/
final class Uploadifyswf extends \System\Web\Controller
{
    const SWF_FILE = 'htmlform/specialelement/swf/uploadify.swf';

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
        $this->swf();
    }

    /**
    * Generates the SWF file
    */
    public function swf()
    {
        $swfFile = new \System\IO\File(PATH_MODULES . self::SWF_FILE);

        $renderer = new \System\Output\Renderer\DataRenderer();
        $renderer->render($swfFile->getContents());
        $surface = \System\Output\RenderSurface::getSurface('\System\Output\BufferSurface');
        $surface->addHeader('Content-Type: application/x-shockwave-flash');
        $surface->setRenderer($renderer);
        $this->setRenderSurface($surface);
    }
}