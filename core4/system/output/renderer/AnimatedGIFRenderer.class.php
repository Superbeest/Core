<?php
/**
* AnimtedGIFRemderer.class.php
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
* Functions to create animated GIF files
* @package \System\Output\Renderer
*/
final class AnimatedGIFRenderer extends \System\Output\Renderer
{
    /**
    * @var array A buffer to keep the image data in, before outputting it to the primary buffer
    */
    private $doubleBuffer = array();

    /**
    * @var array The array which holds the extra parameters for the imagedata.
    */
    private $params = array();

    /**
    * Constants used as in internal index reference
    */
    const PARAMS_INDEX_DELAY = 0;
    const PARAMS_INDEX_DISPOSAL_METHOD = 1;
    const PARAMS_INDEX_TRANSPARENTCOLOR = 2;

    /**
    * The default GIF headers. This must be 6 bytes long
    */
    const GIF_HEADER_TRANSPARENT = 'GIF89a';
    const GIF_HEADER = 'GIF87a';

    /**
    * The default delay between frames in MS
    */
    const DELAY_DEFAULT = 7;

    /**
    * The number of loops when using infinite looping
    */
    const NUMBER_OF_LOOPS_INFINITE = 0;

    /**
    * Replace the previous nontransparent frame with a new one
    */
    const DISPOSAL_UNSPECIFIED = 0;
    /**
    * Just place the next frame on top of the previous, default
    */
    const DISPOSAL_DO_NOT_DISPOSE = 1;
    /**
    * The background color or background tile - rather than a previous frame - shows through transparent pixels. In the GIF specification, you can set a background color.
    */
    const DISPOSAL_RESTORE_TO_BACKGROUND = 2;
    /**
    * Restores to the state of a previous, undisposed frame.
    */
    const DISPOSAL_RESTORE_TO_PREVIOUS = 3;

    /**
    * @var bool Internal variable to tell if the image processed is the firstimage passing through.
    */
    private $firstImage = true;

    /**
    * Adds a frame to the animation. All frames must be of equal size in pixels.
    * The frame may either be a \System\Image\Image type or an absolute filename (string).
    * The delay in frames per second sets the speed for the animation. This speed can thus vary between frames.
    * Internet Explorer slows down GIFs if the framerate is 20 frames per second or higher.
    * There are multiple disposalmethods. They are discussed here: http://www.webreference.com/content/studio/disposal.html
    * Default disposalmethod is DISPOSAL_DO_NOT_DISPOSE to replace the image.
    * The hexcolor will be used (can be in the format of #000000 or 000000) as the transparent color.
    * NOTE: All the images must use the same palette, or they will get distorted.
    * @param mixed An \System\Image\Image type or an absolute filename containing a GIF file
    * @param int The amount of frames per second.
    * @param int The disposal method. Can be any of the DISPOSAL_* values
    * @param string The hexcolor for transparency
    */
    public final function addFrame($source, $delayFPS = \System\Output\Renderer\AnimatedGIFRenderer::DELAY_DEFAULT, $disposalMethod = \System\Output\Renderer\AnimatedGIFRenderer::DISPOSAL_DO_NOT_DISPOSE, $transparentHexColor)
    {
        switch (true)
        {
            case ($source instanceof \System\Image\Image):
                //we create a new output buffer to catch the results of the imagegif function.
                ob_start();
                //imagegif outputs GIF87a or GIF89a depending on transparency usage
                imagegif($source->getImageData());
                $binairyGIFData = ob_get_contents();
                ob_end_clean();

                $this->doubleBuffer[] = $binairyGIFData;
                break;
            case \System\Type::getType($source) == \System\Type::TYPE_STRING:
                $file = new \System\IO\File($source);
                $fileData = $file->getContents();
                $gifHeader = substr($fileData, 0, 6);
                if (($gifHeader != self::GIF_HEADER) &&
                    ($gifHeader != self::GIF_HEADER_TRANSPARENT))
                {
                    throw new \InvalidArgumentException('Could not read input source. The given file does not appear to be a valid GIF file');
                }
                $this->doubleBuffer[] = $fileData;
                break;
            default:
                throw new \InvalidArgumentException('Could not read input source. Expected Image or string (filename)');
        }

        $this->checkGIFConsistancy(end($this->doubleBuffer));
        $this->params[] = array(self::PARAMS_INDEX_DELAY            => $delayFPS,
                                self::PARAMS_INDEX_DISPOSAL_METHOD  => $disposalMethod,
                                self::PARAMS_INDEX_TRANSPARENTCOLOR => $transparentHexColor);
    }

    /**
    * Checks the values of the GIF for consistancy. Some GIF data files have NETSCAPE fields. They are not supported.
    * @param string The data of the GIF image
    */
    private final function checkGIFConsistancy($imageData)
    {
        for ($j = (13 + (3 * (2 << (ord($imageData{10}) & 0x07)))), $k = true; $k; $j++)
        {
            switch ($imageData{$j})
            {
                case '!':
                    if (substr($imageData, ($j + 3), 8) == 'NETSCAPE')
                    {
                        throw new \InvalidArgumentException('The GIF file could not be read properly. Corrupt data?');
                    }
                    break;
                case ';':
                    $k = false;
                    break;
                default:
                    //ignore this byte
            }
        }
    }

    /**
    * Adds a GIF footer to the output buffer
    */
    private final function createGIFFooter()
    {
        $this->addToBuffer(';');
    }

    /**
    * Creates the GIF header. This will be based on the first image and its palette.
    * Also, that type will be used.
    * @param int The number of loops encoded in the image
    */
    private final function createGIFHeader($numberOfLoops)
    {
        $dataMap = 0;
        $firstImage = reset($this->doubleBuffer);
        if (ord($firstImage{10}) & 0x80)
        {
            $dataMap = 3 * (2 << (ord($firstImage{10}) & 0x07));

            $this->addToBuffer(substr($firstImage, 6, 7));
            $this->addToBuffer(substr($firstImage, 13, $dataMap));
            $this->addToBuffer("!\377\13NETSCAPE2.0\3\1" . $this->createGIFWord($numberOfLoops) . "\0");
        }
    }

    /**
    * Encode an int to a usable value. The value will be bitshifted and an & operation is executed to
    * append values to eachother in an inverted way.
    * @param int The value to convert
    */
    private final function createGIFWord($value)
    {
        return (chr($value & 0xFF) . chr(($value >> 8) & 0xFF));
    }

    /**
    * Outputs the given image object as a JPG image. The output of this renderer can be written to any RenderSurface.
    * @param int The number of loops for the movie. Must be positive
    */
    public final function render()
    {
        $args = func_get_args();

        $val = new \System\Security\Validate();
        if ((count($args) != 1) ||
            ($val->isInt($args[0], 'loops', 0, \System\Math\Math::MAXINT, true) != \System\Security\ValidateResult::VALIDATE_OK))
        {
            throw new \InvalidArgumentException('Invalid amount of arguments given.');
        }

        $numberOfLoops = $args[0];

        if (count($this->doubleBuffer) == 0)
        {
            throw new \System\Error\Exception\SystemException('Expected at least 1 image source to output');
        }

        $this->addToBuffer(self::GIF_HEADER_TRANSPARENT);
        $this->createGIFHeader($numberOfLoops);

        for ($x = 0; $x < count($this->doubleBuffer); $x++)
        {
            $this->addFrameToOutputBuffer(
                    $this->doubleBuffer[$x],
                    $this->params[$x][self::PARAMS_INDEX_DELAY],
                    $this->params[$x][self::PARAMS_INDEX_DISPOSAL_METHOD],
                    $this->params[$x][self::PARAMS_INDEX_TRANSPARENTCOLOR]);
        }
        $this->createGIFFooter();
    }

    /**
    * Adds a frame to the output buffer for dislay.
    * @param string The raw image data
    * @param int The delay between the frames
    * @param int The disposal method for each frame
    * @param string The hexcolor used for transparency
    */
    private final function addFrameToOutputBuffer($imageData, $delay, $disposalMethod, $transparentHexColor)
    {
        $str = 13 + (3 * (2 << (ord($imageData{10}) & 0x07)));

        $end = strlen($imageData) - $str - 1;
        $tmp = substr($imageData, $str, $end);

        //we get the first image from our double buffer to build with.
        $firstImage = reset($this->doubleBuffer);
        $globalLength = 2 << (ord($firstImage{10}) & 0x07);
        $length = 2 << (ord($imageData{10}) & 0x07);

        $globalRGB = substr($firstImage, 13, 3 * (2 << (ord($firstImage{10}) & 0x07)));
        $rgb = substr($imageData, 13, 3 * (2 << (ord($imageData{10}) & 0x07)));

        $extension = "!\xF9\x04" . chr(($disposalMethod << 2) + 0) . chr(($delay >> 0) & 0xFF) . chr(($delay >> 8) & 0xFF) . "\x0\x0";

        //Convert the hexcolor to RGB and put them in one large integer value
        \System\Image\ColorConversion::hexToRGB($transparentHexColor, $r, $g, $b);
        $color = $r | ($g << 8) | ($b << 16);

        if (ord($imageData{10}) & 0x80)
        {
            for ($j = 0; $j < (2 << (ord($imageData{10}) & 0x07)); $j++)
            {
                if ((ord($rgb{3 * $j + 0}) == (($color >> 16) & 0xFF)) &&
                    (ord($rgb{3 * $j + 1}) == (($color >> 8) & 0xFF)) &&
                    (ord($rgb{3 * $j + 2}) == (($color >> 0) & 0xFF)))
                {
                    $extension = "!\xF9\x04" . chr(($disposalMethod << 2) + 1) . chr(($delay >> 0) & 0xFF) . chr(($delay >> 8) & 0xFF) . chr($j) . "\x0";
                    break;
                }
            }
        }

        switch ($tmp{0})
        {
            case '!':
                $img = substr($tmp, 8, 10);
                $tmp = substr($tmp, 18, strlen($tmp) - 18);
                break;
            case ',':
                $img = substr($tmp, 0, 10);
                $tmp = substr($tmp, 10, strlen($tmp) - 10);
                break;
            default:
                //we ignore other cases
        }

        if ((ord($imageData{10}) & 0x80) &&
            ($this->firstImage))
        {
            if ($globalLength == $length)
            {
                //we check the image color palette with the animations palette
                if ($this->compareGIFBlock($globalRGB, $rgb, $globalLength))
                {
                    $this->addToBuffer($extension . $img . $tmp);
                }
                else
                {
                    $byte = ord($img{9});
                    $byte |= 0x80;
                    $byte &= 0xF8;
                    $byte |= (ord($firstImage{10}) & 0x07);
                    $img{9} = chr($byte);
                    $this->addToBuffer($extension . $img . $rgb . $tmp);
                }
            }
            else
            {
                $byte = ord($img{9});
                $byte |= 0x80;
                $byte &= 0xF8;
                $byte |= (ord($imageData{10}) & 0x07);
                $img{9} = chr($byte);
                $this->addToBuffer($extension . $img . $rgb . $tmp);
            }
        }
        else
        {
            $this->addToBuffer($extension . $img . $tmp);
        }

        $this->firstImage = false;
    }

    /**
    * Compare two color blocks with eachother for palette matching.
    * @param string The first block to compare
    * @param string The second block to compare with the first one
    * @param int The lenght of the bytes to compare
    */
    private final function compareGIFBlock($block1, $block2, $length)
    {
        for ($i = 0; $i < $length; $i++)
        {
            if (($block1{3 * $i + 0} != $block2{3 * $i + 0}) ||
                ($block1{3 * $i + 1} != $block2{3 * $i + 1}) ||
                ($block1{3 * $i + 2} != $block2{3 * $i + 2}))
            {
                return false;
            }
        }

        return true;
    }

    /**
    * Returns the header suggestions for the current Renderer.
    * Specific Renderers may override this function to provide render suggestions to the RenderSurface.
    * Do note that the RenderSurface may ignore the given suggestions.
    * @param \System\Collection\Vector The header suggestions.
    */
    public final function getHeaderSuggestions()
    {
        return new \System\Collection\Vector('Content-Type: image/gif');
    }
}