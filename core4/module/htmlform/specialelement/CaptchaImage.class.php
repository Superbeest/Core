<?php
/**
* CaptchaImage.class.php
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
* Generates the captcha image for the system. It is based on the regular image system, so it can easily be adapted
* @package \Module\HTMLForm\SpecialElement
*/
final class CaptchaImage extends \System\Image\GD\Image
{
    const CAPTCHA_HEIGHT_FACTOR = 2.5;
    const CAPTCHA_WIDTH_FACTOR = 1.5;

    const NOISE_CHAR_START = 45;
    const NOISE_CHAR_END = 250;
    const NOISE_MIN_AMOUNT = 25;
    const NOISE_MAX_AMOUNT = 65;
    const NOISE_MAX_ROTATION = 359;

    const LINES_AMOUNT = 10;

    private $ttfFiles;
    private $currentFont = 0;

    private $fontFolder;
    private $minFontSize;
    private $maxFontSize;
    private $maxRotation;
    private $width;
    private $height;

    private $refreshIcon = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACtElEQVR4nIWTTWhUZxiFn+/+z71zZ4ZJxpRWaBwaNSqNGkJBRaS4UBdBCwUpFLt04aIlCN24cqFisxAEt4oLQdCNlNJgQaloRZAKRkjVxIXGFm3HMfNz79z7fW8XkZBgoWd9eA6c874qPvpEFk5/olimvsm/j5tc71IFb8fABwGNFLTv3tq51rpxZbM6ttyrOPxQ/KRLen5MAbiHp+XAkQ0kBioWhD6EDmwIYLwMRx7D5RG1FOigNdH4KCa9I3a1zKavh3nZhMgBQtA5+Aqm2+AoODsE+neRK5sXIQ69hLeZpvjlGLXQot0QopLCi0BsaOXwqgOjq+BFBmdfwbm1wDuIg9bkGpJeTu7aYAlx6HLz/Bx57BHXQvKeYcrYTB6q0OjALwuLkPs/izhkHej1yGybRk/hZIZ789BqttE/1FV7WWETNGTyUIWOgbkcjAaHPIO0jVYF9oz5fF61GQng1NAmpvqfSevE4FJhpYLF9xffUF5XQQTKvqD6Jx5KHHkgwtztGShWsIc/xkfonBxcMe9/6X8NAN7EU+llhrheRRuLPZ+V0FrwCxaK/deELRsh12BbBDN/kVzatgQOjs7K+FdrOLAa6j7MpvCoBZUALtzXOLxtwUdFwALXgen5FenJ6br6c7Qpr/tLXH0BJRdqBUg1SJrjYDJoJ2AJdCyS8RECfpWeFoJaBWMMd68/Y92WT3mTgg04FqQG8iRf7KBw8Dfp7h6EhS7YDpRj3IYCMWRdzfD2PlwNAyFEHvRHULDhp9utZSV+8aOwdyskGeQ5KBdqAwSWoVpQ+JbNqppN83mXP6Zm2bivTutld+UKwTcPJNlaXTx6rSEqglMkLjmEkUXz6iMsz6ZzZr0Kv52R6lDf+zPGE4+PZ/90diUL8ztQCozBK354y6sEN1pn1q945ei7GfkXNcgXOztaodwAAAAASUVORK5CYII=';

    public function __construct_5($code, $fontFolder, $minFontSize, $maxFontSize, $maxRotation)
    {
        if (!function_exists('imagettftext'))
        {
            throw new \System\Error\Exception\SystemException('Requires FreeType support. Please enable PHP FreeType');
        }

        $this->fontFolder = $fontFolder;
        $this->ttfFiles = \System\IO\Directory::walkDir($fontFolder, new \System\Collection\Vector('ttf'));

        $this->width = (strlen($code) + 1) * intval(($minFontSize + $maxFontSize) / self::CAPTCHA_WIDTH_FACTOR);
        $this->height = intval(self::CAPTCHA_HEIGHT_FACTOR * $maxFontSize);
        $this->minFontSize = $minFontSize;
        $this->maxFontSize = $maxFontSize;
        $this->maxRotation = $maxRotation;

        $this->image = imagecreatetruecolor($this->width, $this->height);
        $bgcolor = imagecolorallocate($this->image, mt_rand(224, 255), mt_rand(224, 255), mt_rand(224, 255));
        imagefill($this->image, 0, 0, $bgcolor);

        $this->createBackground();

        $chars = str_split($code);

        $xPos = mt_rand($minFontSize, $maxFontSize);
        foreach ($chars as $char)
        {
            $size = mt_rand($minFontSize, $maxFontSize);
            $rotation = mt_rand($maxRotation * -1, $maxRotation);
            $yPos = mt_rand(intval($size * self::CAPTCHA_WIDTH_FACTOR), intval($this->height - ($size / 4)));

            $r = mt_rand(0, 127);
            $g = mt_rand(0, 127);
            $b = mt_rand(0, 127);
            $color = imagecolorallocate($this->image, $r, $g, $b);
            $shadow = imagecolorallocate($this->image, $r + 127, $g + 127, $b + 127);

            $ttfFile = $this->getNextTTFFile();

            imagettftext($this->image, $size, $rotation, $xPos + mt_rand(1, 5), $yPos + mt_rand(0, 5), $shadow, $ttfFile, $char);
            imagettftext($this->image, $size, $rotation, $xPos, $yPos, $color, $ttfFile, $char);

            $xPos += intval($size + ($minFontSize / 3));
        }

        $watermarkImage = imagecreatefromstring(base64_decode($this->refreshIcon));

        $origSX = imagesx($this->image);
        $origSY = imagesy($this->image);

        $waterSX = imagesx($watermarkImage);
        $waterSY = imagesy($watermarkImage);

        $destX = $origSX - $waterSX - 5;
        $destY = $origSY - $waterSY - 5;

        imagecopy($this->image, $watermarkImage, $destX, $destY, 0, 0,$waterSX, $waterSY);
        imagedestroy($watermarkImage);
    }

    /**
    * Generates the background for the captcha
    */
    private function createBackground()
    {
        switch (mt_rand(0, 1))
        {
            case 0:
                $this->createBackgroundGrid();
                $this->drawLines();
                break;
            case 1:
                $this->createBackgroundNoise();
                break;
            default:
                throw new \System\Error\Exception\MethodNotImplementedException('The given random value does not correspond to a backdrop generator');
        }
    }

    /**
    * Generates a random pattern of random colored random characters for the background.
    */
    private function createBackgroundNoise()
    {
        $maxNoise = mt_rand(self::NOISE_MIN_AMOUNT, self::NOISE_MAX_AMOUNT);
        for ($i = 0; $i < $maxNoise; $i++)
        {
            $size = intval(rand(intval($this->minFontSize / 2.3), intval($this->maxFontSize / 1.7)));
            $rotation = intval(rand(0, self::NOISE_MAX_ROTATION)); //100% rotation
            $xPos = intval(rand(0, $this->width));
            $yPos = intval(rand(0, intval($this->height - ($size / 5))));

            $r = mt_rand(160, 224);
            $g = mt_rand(160, 224);
            $b = mt_rand(160, 224);
            $color = imagecolorallocate($this->image, $r, $g, $b);

            $char = chr(intval(rand(self::NOISE_CHAR_START, self::NOISE_CHAR_END)));
            imagettftext($this->image, $size, $rotation, $xPos, $yPos, $color, $this->getNextTTFFile(), $char);
        }
    }

    /**
    * Generates a grid for the background with random colored lines.
    */
    private function createBackgroundGrid()
    {
        for ($x = 0; $x < $this->width; $x += intval($this->minFontSize / self::CAPTCHA_WIDTH_FACTOR))
        {
            $r = mt_rand(100, 184);
            $g = mt_rand(100, 184);
            $b = mt_rand(100, 184);
            $color = imagecolorallocate($this->image, $r, $g, $b);
            imageline($this->image, $x, 0, $x, $this->width, $color);
        }

        for ($y = 0; $y < $this->height; $y += intval($this->minFontSize / self::CAPTCHA_WIDTH_FACTOR))
        {
            $r = mt_rand(160, 224);
            $g = mt_rand(160, 224);
            $b = mt_rand(160, 224);
            $color = imagecolorallocate($this->image, $r, $g, $b);
            imageline($this->image, 0, $y, $this->width, $y, $color);
        }
    }

    /**
    * Gets a new TTF file from the vector. Iterates through all the current fonts
    * @return string The file to use as a font
    */
    private function getNextTTFFile()
    {
        if ($this->currentFont >= count($this->ttfFiles))
        {
            $this->currentFont = 0;
        }

        $file = $this->ttfFiles[$this->currentFont];
        $this->currentFont++;

        return $file->getFullPath();
    }

    /**
    * Draws background lines
    */
	private function drawLines()
    {
    	$amountOfLines = self::LINES_AMOUNT;
    	$sizeX = imagesx($this->image);
    	$sizeY = imagesy($this->image);

        for ($line = 0; $line < $amountOfLines; $line++)
        {
            $x = $sizeX * (1 + $line) / ($amountOfLines + 1);
            $x += (0.5 - $this->frand()) * $sizeX / $amountOfLines;
            $y = mt_rand($sizeY * 0.1, $sizeY * 0.9);

            $theta = ($this->frand() - 0.5) * M_PI * 0.7;
            $w = $sizeX;
            $len = mt_rand($w * 0.4, $w * 0.7);
            $lwid = mt_rand(0, 2);

            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos($theta);
            $dy = $step * sin($theta);
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
            $x0 = $x - 0.5 * $len * cos($theta);
            $y0 = $y - 0.5 * $len * sin($theta);

            $ldx = round(-$dy * $lwid);
            $ldy = round($dx * $lwid);

            for ($i = 0; $i < $n; $i++)
            {
                $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                imagefilledrectangle($this->image, $x, $y, $x + $lwid, $y + $lwid, imagecolorallocate($this->image, mt_rand(100, 184), mt_rand(100, 184), mt_rand(100, 184)));
            }
        }
    }

    /**
    * Returns a random floating value
    * @return float A random number between 0.0001 and 0.9999
    */
    private function frand()
    {
        return 0.0001 * mt_rand(0,9999);
    }

}