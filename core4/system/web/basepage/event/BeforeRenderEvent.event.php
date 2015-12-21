<?php
/**
* BeforeRenderEvent.event.php
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


namespace System\Web\BasePage\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Checks if the title, keywords and description have been set in the page
* @package \System\Web\BasePage\Event
*/
class BeforeRenderEvent extends \System\Base\StaticBase
{
	/**
	* The before render event
	* @param \System\Cache\PageCache\Event\OnBeforeRenderEvent $event
	*/
    public static final function render(\System\Cache\PageCache\Event\OnBeforeRenderEvent $event)
    {
        $page = $event->getPage();

        $title = $page->getTitle();
        $description = $page->getDescription();
        $keywords = $page->getKeywords();

        $xml = $event->getXmlTree();

        if (((empty($title)) && (!isset($xml->title))) ||
        	((empty($description)) && (!isset($xml->description))) ||
        	((empty($keywords)) && (!isset($xml->keywords))))
        {
            throw new \System\Error\Exception\SystemException('The given page does not have proper title (' . $title . '), description (' . $description . '), or keywords (' . $keywords . ') set');
        }
    }
}
