<?php
/**
* Minify.class.php
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


namespace System\Web\Minify\HTML;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Minimized the HTML output
* @package \System\Web\Minify\HTML
*/
class Minify extends \System\Base\StaticBase implements \System\Web\Minify\iMinify
{
    /**
    * Minifies the given content according to the given options.
    * @param mixed The content to minify
    * @param \System\Collection\Map The options to use for minification or null
    * @return mixed The minified content
    */
    public static function minify($content, \System\Collection\Map $options = null)
    {
        /*$content = str_replace("\t", '', $content);
        $content = str_replace("\n\r", '', $content);
        $content = str_replace("\n", '', $content);
        $content = str_replace("\r", '', $content);
        $content = preg_replace('/> {2,}</', '><', $content);
        $content = str_replace(' />', '/>', $content);

        return $content;*/

        return \System\Web\Minify\HTML\Minify_HTML::minify($content);
    }
}
