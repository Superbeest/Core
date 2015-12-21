<?php
/**
* XSLTRenderer.class.php
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
* This class provides functionality to combine XML and XSL files to HTML output
* @package \System\Output\Renderer
*/
class XSLTRenderer extends \System\Output\Renderer
{
	/**
	* @publicget
	* @publicset
	* @var array The registered PHP function for XSL
	*/
	protected static $registeredPHPFunctions = array();

	/**
	* Adds a php function call to the registed functions.
	* @param string The call to be made
	*/
	public static final function addRegisteredPHPFunction($functioncall)
	{
		self::$registeredPHPFunctions[] = $functioncall;
	}

    /**
    * Converts the given XML tree and the given XSL file to a (HTML) file, with respect to the given XSL parameters.
    * The given XMLTree parameter may be of the SimpleXMLElement or the DOMDocument type, however, the latter is the fastest.
    * The generated output may be considered to be formatted properly.
    * @param mixed The XML Tree as a SimpleXMLElement or a DOMDocument
    * @param mixed The fullpath to the XSL file, or an instanced \SimpleXMLElement XSL tree
    * @param \System\Collection\Map The map with the parameters, or null for no parameters or default
    */
    public final function render()
    {
        $args = func_get_args();

        if (count($args) != 3)
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        list($xmlTree, $xslFile, $parameters) = $args;

        $processor = new \XSLTProcessor();

        //register the php functions
        if (count(self::$registeredPHPFunctions) > 0)
        {
        	$processor->registerPHPFunctions(self::$registeredPHPFunctions);
		}

		//load the xsl file intro a dom
	    $xsl = new \DOMDocument();
		if ($xslFile instanceof \SimpleXMLElement)
		{
			$xsl->loadXML($xslFile->asXML());
		}
		else
		{
	        if (!file_exists($xslFile))
	        {
	            throw new \System\Error\Exception\FileNotFoundException('XSL File: ' . $xslFile . ' cannot be found');
	        }

	        $xsl->load($xslFile);
		}

        //attach the xsl dom to the processor
        $processor->importStylesheet($xsl);

        //when we run as a local debug system, we output the profiling information
        if (defined('DEBUG'))
        {
            $processor->setProfiling(PATH_LOGS . 'XSLTRenderProfiling.txt');
        }

        $dom = new \DOMDocument();
        switch (true)
        {
            case $xmlTree instanceof \SimpleXMLElement:
                //we need to convert to a domdocument
                $dom = \System\XML\XML::convertSimpleXMLElementToDOMDocument($xmlTree);
                break;
            case $xmlTree instanceof \DOMDocument:
                //no conversion needed
                break;
            default:
                throw new \InvalidArgumentException('Given XML tree is of non supported tree type: ' . get_class($xmlTree));
        }

        $this->preprocessParameters($parameters);

        //we do not need any namespaces
        $processor->setParameter('', $parameters->getArrayCopy());

        $output = $processor->transformToXML($dom);
        if (!$output)
        {
            throw new \Exception('Could not transform the given XML and XSL to a valid HTML page');
        }

        if (MINIFY_ENABLE)
        {
            $output = \System\Web\Minify\HTML\Minify::minify($output);
        }

        $this->addToBuffer($output);
    }

    /**
    * Checks each parameter for specific data. If the data is a callback, then the output of that function will be inserted as a value.
    * This will effectively replace the map alltogether
    * @param \System\Collection\Map The parameters by reference.
    */
    private final function preprocessParameters(\System\Collection\Map &$parameters)
    {
        $map = new \System\Collection\Map();
        foreach ($parameters as $key=>$parameter)
        {
        	if ((is_array($parameter)) &&
            	(is_callable($parameter)))
            {
                $parameter = call_user_func($parameter);
            }
            $map->$key = $parameter;
        }
        $parameters = $map;
    }

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public function getHeaderSuggestions()
    {
        return new \System\Collection\Vector('Content-type: text/html; charset=UTF-8');
    }
}
