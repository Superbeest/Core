<?php
/**
* APIGen.ctrl.php
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


namespace System\System;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Generates the API doc
* @package \System\System
*/
class APIGen extends \System\Web\Controller
{
    /**
    * The docfolder will be used as the root for the API doc
    */
    const DOC_FOLDER = 'doc/';

    /**
    * This function is the default entry for a controller. When a controller is called without any parameters,
    * then the control is transferred to the defaultAction function.
    */
    public final function defaultAction()
    {
    }

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

    /**
    * Generates the API doc in the DOC_FOLDER, this only works in debug mode
    */
    public final function generateAPI()
    {
		if (defined('DEBUG'))
		{
        	$dir = new \System\IO\Directory(PATH_CONTENT . self::DOC_FOLDER);
        	\System\Inspection\APIGen::generateAPIDoc($dir);
		}

        $this->setRenderMode(\System\Web\Controller::RENDERER_DISABLED);
    }
}
