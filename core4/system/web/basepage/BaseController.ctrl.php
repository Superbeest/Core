<?php
/**
* BaseController.ctrl.php
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


namespace System\Web\BasePage;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* A simple parent class to encapsulate common functionality
* @package \System\Web\BasePage
*/
abstract class BaseController extends \System\Web\Controller
{
    /**
    * Gets the get request object.
    */
    protected final function getGet()
    {
        return new \System\HTTP\Request\Get();
    }

    /**
	* Output the given Page and set the renderer and rendersurface
	* @param \System\Web\BasePage\Page\BasePage The Page to output
	*/
	protected function renderDefault(\System\Web\BasePage\Page\BasePage $page)
    {
        $renderer = $page->getRenderer();
        $surface = \System\Output\RenderSurface::getSurface('\System\Output\GZIPBufferSurface');
        $surface->setRenderer($renderer);
        $this->setRenderSurface($surface);
    }

    /**
	* Redirect to the given url
	* @param string The url to redirect to
	*/
    protected function renderRedirect($url)
    {
		$renderer = new \System\Output\Renderer\RedirectRenderer();
        $renderer->render($url);
        $surface = \System\Output\RenderSurface::getSurface('\System\Output\GZIPBufferSurface');
        $surface->setRenderer($renderer);
        $this->setRenderSurface($surface);
	}

	/**
	* Output the given XML and set the renderer and rendersurface
	* @param \SimpleXMLElement The XML to output
	*/
    protected function renderXML($xml)
    {
    	$renderer = new \System\Output\Renderer\XMLRenderer();
    	$renderer->render($xml);

    	$renderSurface = \System\Output\RenderSurface::getSurface('\System\Output\GZIPBufferSurface');
    	$renderSurface->setRenderer($renderer);
    	$this->setRenderSurface($renderSurface);
	}

	/**
	* Output the given JSON and set the renderer and rendersurface
	* @param mixed The object to output as JSON
	*/
	protected function renderJSON($json)
	{
		$renderer = new \System\Output\Renderer\JSONRenderer();
    	$renderer->render($json, 0);

    	$renderSurface = \System\Output\RenderSurface::getSurface('\System\Output\GZIPBufferSurface');
    	$renderSurface->setRenderer($renderer);
    	$this->setRenderSurface($renderSurface);
	}
}
