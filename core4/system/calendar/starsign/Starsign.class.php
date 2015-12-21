<?php
/**
* Starsign.class.php
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


namespace System\Calendar\Starsign;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Represents a Starsign object. Uses factory pattern to create a starsign object
* corresponding the given Time
* @package \System\Calendar\Starsign
*/
class Starsign extends \System\Base\StaticBase
{
    /**
    * This represents the startime of a starsign object
    * @var \System\Calendar\Time
    */
    protected $startTime = null;

    /**
    * This represents the stoptime of a starsign object
    * @var \System\Calendar\Time
    */
    protected $stopTime = null;

    private static $capricorn = null;
    private static $aquarius = null;
    private static $pisces = null;
    private static $aries = null;
    private static $taurus = null;
    private static $gemini = null;
    private static $cancer = null;
    private static $leo = null;
    private static $virgo = null;
    private static $libra = null;
    private static $scorpio = null;
    private static $sagittarius = null;

    /**
    * Returns the current startTime
    * @return The startTime as \System\Calendar\Time
    */
    public final function getStartTime()
    {
        return $this->startTime;
    }

    /**
    * Returns the current stopTime
    * @return The stopTime as \System\Calendar\Time
    */
    public final function getStopTime()
    {
        return $this->stopTime;
    }

    /**
    * Function for getting the Starsign object for the corresponding Time
    * (Capricorn, Aquarius, Pisces, Aries, Taurus, Gemini, Cancer, Leo, Virgo, Libra, Scorpio, Sagittarius)
    * @param \System\Calendar\Time the time for which the starsign will be calculated
    * @return a starsign object
    */
    public final static function getStarsign(\System\Calendar\Time $time)
    {
        if (self::$capricorn == null)
        {
            self::$capricorn = new \System\Calendar\Starsign\Capricorn();

            if (self::$capricorn->containsDate($time))
            {
                return self::$capricorn;
            }
        }

        if (self::$aquarius == null)
        {
            self::$aquarius = new \System\Calendar\Starsign\Aquarius();

            if (self::$aquarius->containsDate($time))
            {
                return self::$aquarius;
            }
        }

        if (self::$pisces == null)
        {
            self::$pisces = new \System\Calendar\Starsign\Pisces();

            if (self::$pisces->containsDate($time))
            {
                return self::$pisces;
            }
        }

        if (self::$aries == null)
        {
            self::$aries = new \System\Calendar\Starsign\Aries();

            if (self::$aries->containsDate($time))
            {
                return self::$aries;
            }
        }

        if (self::$taurus == null)
        {
            self::$taurus = new \System\Calendar\Starsign\Taurus();

            if (self::$taurus->containsDate($time))
            {
                return self::$taurus;
            }
        }

        if (self::$gemini == null)
        {
            self::$gemini = new \System\Calendar\Starsign\Gemini();

            if (self::$gemini->containsDate($time))
            {
                return self::$gemini;
            }
        }

        if (self::$cancer == null)
        {
            self::$cancer = new \System\Calendar\Starsign\Cancer();

            if (self::$cancer->containsDate($time))
            {
                return self::$cancer;
            }
        }

        if (self::$leo == null)
        {
            self::$leo = new \System\Calendar\Starsign\Leo();

            if (self::$leo->containsDate($time))
            {
                return self::$leo;
            }
        }

        if (self::$virgo == null)
        {
            self::$virgo = new \System\Calendar\Starsign\Virgo();

            if (self::$virgo->containsDate($time))
            {
                return self::$virgo;
            }
        }

        if (self::$libra == null)
        {
            self::$libra = new \System\Calendar\Starsign\Libra();

            if (self::$libra->containsDate($time))
            {
                return self::$libra;
            }
        }

        if (self::$scorpio == null)
        {
            self::$scorpio = new \System\Calendar\Starsign\Scorpio();

            if (self::$scorpio->containsDate($time))
            {
                return self::$scorpio;
            }
        }

        if (self::$sagittarius == null)
        {
            self::$sagittarius = new \System\Calendar\Starsign\Sagittarius();

            if (self::$sagittarius->containsDate($time))
            {
                return self::$sagittarius;
            }
        }
    }

    /**
    * Function to check if the given time falls between starttime and stoptime of the current starsign
    * @param \System\Calendar\Time the time to check
    * @return boolean indicating if the given time falls betwqeen starttime and stoptime of the current starsign
    */
    protected final function containsDate(\System\Calendar\Time $time)
    {
        return (
                    (
                        ($time->getMonth() == $this->startTime->getMonth()) &&
                        ($time->GetDay() >= $this->startTime->getDay())
                    )
                        ||
                    (
                        ($time->getMonth() == $this->stopTime->getMonth()) &&
                        ($time->getDay() <= $this->stopTime->getDay())
                    )
                );
    }
}