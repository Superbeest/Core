<?php
/**
* XMLRenderer.class.php
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


namespace System\Output\Renderer;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* A renderer for outputting XML
* @package \System\Output\Renderer
*/
class XMLRenderer extends \System\Output\Renderer
{
    /**
    * Outputs the given XML tree
    * The given XMLTree parameter may be of the SimpleXMLElement or the DOMDocument type, however, the latter is the fastest.
    * @param mixed The XML Tree as a SimpleXMLElement or a DOMDocument
    */
    public final function render()
    {
        $args = func_get_args();

        if (count($args) != 1)
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $xml = $args[0];

        if ((!($xml instanceof \SimpleXMLElement)) &&
            (!($xml instanceof \DOMDocument)))
        {
            throw new \InvalidArgumentException('Invalid XML tree given');
        }

        $output = '';

        switch (true)
        {
            case $xml instanceof \SimpleXMLElement:
                /** @var \SimpleXMLElement */
                $xml = $xml;
                $output = $xml->asXML();
                break;
            case $xml instanceof \DOMDocument:
                $output = $xml->saveXML();
                break;
            default:
                throw new \Exception('Could not read the XML tree');
        }

        $this->addToBuffer($output);
    }

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public final function getHeaderSuggestions()
    {
        return new \System\Collection\Vector('Content-Type: application/xml');
    }
}