<?php
/**
* Time.class.php
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


namespace System\Calendar;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Represents a time object
* @package \System\Calendar
*/
class Time extends \System\Base\Base
{
	/**
	* Undefined time
	*/
	const TIME_UNDEFINED = '0';

    /**
    * The amount of seconds in a minute
    */
    const SECONDS_IN_MINUTE         = 60;
    /**
    * The amount of minutes in an hour
    */
    const MINUTES_IN_HOUR           = 60;
    /**
    * The amount of seconds in an hour
    * \System\Calendar\Time::SECONDS_IN_MINUTE * \System\Calendar\Time::MINUTES_IN_HOUR;
    */
    const SECONDS_IN_HOUR           = 3600;
    /**
    * The amount of hours in a day
    */
    const HOURS_IN_DAY              = 24;
    /**
    * The amount of seconds in a day. This value is not 100% accurate for daytime calculations.
    * It does not take into account the leapyear.
    * \System\Calendar\Time::SECONDS_IN_HOUR * \System\Calendar\Time::HOURS_IN_DAY;
    */
    const SECONDS_IN_DAY            = 86400;
    /**
    * The amount of days in a week
    */
    const DAYS_IN_WEEK              = 7;
    /**
    * The approximate amount of seconds in a week. This value is not 100% accurate for daytime calculations.
    * It does not take into account the leapyear.
    * \System\Calendar\Time::SECONDS_IN_DAY * \System\Calendar\Time::DAYS_IN_WEEK
    */
    const SECONDS_IN_WEEK           = 604800;
    /**
    * The amount of months in a year
    */
    const MONTHS_IN_YEAR            = 12;
    /**
    * The amount of seconds in a year. This value includes leap years and can thus be considered accurate.
    */
    const SECONDS_IN_YEAR           = 31556926;

    /**
    * @var int Holds the current timestamp
    */
    protected $timestamp = 0;

	/**
	* Implements magic __sleep function to export the timestamp
	* @return array The params to export
	*/
	public final function __sleep()
	{
		return array('timestamp');
	}

    /**
    * Creates a new Time object with the current time as its base.
    */
    public final function __construct_0()
    {
        $this->timestamp = time();
    }

    /**
    * Creates a new Time object
    * @param string Requires a UNIX timestamp.
    */
    public final function __construct_1($timestamp)
    {
        if (is_numeric($timestamp))
        {
            $this->timestamp = $timestamp;
        }
        else
        {
            throw new \InvalidArgumentException('given timestamp is not a valid timestamp: ' . $timestamp);
        }
    }

    /**
    * Creates a new Time object
    * @param string Requires a month.
    * @param string Requires a day.
    */
    public final function __construct_2($month = 1, $day = 1)
    {
        if (($month > 0) &&
            ($month <= 12) &&
            ($day > 0) &&
            ($day <= 31))
        {
            $this->timestamp = strtotime('1970-' . $month . '-' . $day);
        }
        else
        {
            throw new \InvalidArgumentException('given information is not a valid time: ' . $month . '-' . $day);
        }
    }

    /**
    * Creates a new Time object
    * @param string Requires a year.
    * @param string Requires a month.
    * @param string Requires a day.
    */
    public final function __construct_3($year = 1970, $month = 1, $day = 1)
    {
        if ((mb_strlen($year) == 4) &&
            ($month > 0) &&
            ($month <= 12) &&
            ($day > 0) &&
            ($day <= 31))
        {
            $this->timestamp = strtotime($year . '-' . $month . '-' . $day);
        }
        else
        {
            throw new \InvalidArgumentException('given information is not a valid time: ' . $year . '-' . $month . '-' . $day);
        }
    }

    /**
    * Creates a new Time object
    * @param string Requires a year.
    * @param string Requires a month.
    * @param string Requires a day.
    * @param string Requires a hour.
    * @param string Requires a minute.
    */
    public final function __construct_5($year = 1970, $month = 1, $day = 1, $hour = 0, $minute = 0)
    {
        if ((mb_strlen($year) == 4) &&
            ($month > 0) &&
            ($month <= 12) &&
            ($day > 0) &&
            ($day <= 31) &&
            ($hour >= 0) &&
            ($hour <= 23) &&
            ($minute >= 0) &&
            ($minute < 60))
        {
            $this->timestamp = strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
        }
        else
        {
            throw new \InvalidArgumentException('given information is not a valid time: ' . $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
        }
    }

    /**
    * Creates a new Time object
    * @param string Requires a year.
    * @param string Requires a month.
    * @param string Requires a day.
    * @param string Requires a hour.
    * @param string Requires a minute.
    * @param string Requires a second.
    */
    public final function __construct_6($year = 1970, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0)
    {
        if ((mb_strlen($year) == 4) &&
            ($month > 0) &&
            ($month <= 12) &&
            ($day > 0) &&
            ($day <= 31) &&
            ($hour >= 0) &&
            ($hour <= 23) &&
            ($minute >= 0) &&
            ($minute < 60) &&
            ($second >= 0) &&
            ($second < 60))
        {
            $this->timestamp = strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second);
        }
        else
        {
            throw new \InvalidArgumentException('given information is not a valid time: ' . $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second);
        }
    }

    /**
    * Returns the current Time object as a string in the requested format
    * @param string The format as accepted by the date() function
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The formatted date string or false on failure
    */
    public final function getFormat($format, $timezone = TIMEZONE_IDENTIFIER)
    {
        if ($timezone != TIMEZONE_IDENTIFIER)
        {
            $dtz = new \DateTimeZone($timezone);
            $dt = new \DateTime('@' . $this->timestamp);
            $dt = $dt->setTimezone($dtz);
            return $dt->format($format);
        }

        return date($format, $this->timestamp);
    }

	/**
	* A fully RFC 2822 formatted date, like: Thu, 21 Dec 2000 16:01:07 +0200
	* @param string The timezone format. The default is TIMEZONE_IDENTIFIER
	* @return string The formatted RFC2822 date
	*/
    public final function toFullTime($timezone = TIMEZONE_IDENTIFIER)
    {
    	return $this->getFormat('r', $timezone);
	}

    /**
    * Returns the time as dd-mm-yyyy
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string dd-mm-yyyy
    */
    public final function toDDMMYYYY($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('d-m-Y', $timezone);
    }

    /**
    * Returns the time as yyyy-mm-dd
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string yyyy-mm-dd
    */
    public final function toYYYYMMDD($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('Y-m-d', $timezone);
    }

    /**
    * Returns the time as yyyy-mm-dd hh:mm:ss
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string yyyy-mm-dd hh:mm:ss
    */
    public final function toYYYYMMDDHHMMSS($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('Y-m-d H:i:s', $timezone);
    }

    /**
    * Returns the time as yyyy-mm-ddThh:mm:ss
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string yyyy-mm-dd hh:mm:ss
    */
    public final function toYYYYMMDDtHHMMSS($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('Y-m-d', $timezone) . 'T' . $this->getFormat('H:i:s', $timezone);
    }

    /**
    * Returns the UNIX timestamp
    * @return int The time as a UNIX timestamp
    */
    public final function toUNIX()
    {
        return $this->timestamp;
    }

    /**
    * Gives the day of the week the current Time lies in.
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string 1 (for Monday) through 7 (for Sunday)
    */
    public final function getDayOfWeek($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('N', $timezone);
    }

    /**
    * Returns the current Year
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The year as String yyyy
    */
    public final function getYear($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('Y', $timezone);
    }

    /**
    * Returns the current Month
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The Month as String mm
    */
    public final function getMonth($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('m', $timezone);
    }

    /**
    * Returns the current Day
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The Day as String dd
    */
    public final function getDay($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('d', $timezone);
    }

    /**
    * Returns the current Hour
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The Hour as String hh
    */
    public final function getHour($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('H', $timezone);
    }

    /**
    * Returns the current Minute
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The Minute as String mm
    */
    public final function getMinute($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('i', $timezone);
    }

    /**
    * Returns the current Second
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string The Second as String ss
    */
    public final function getSecond($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('s', $timezone);
    }

    /**
    * Returns the current weeknumber
    * @param string The timezone format. The default is TIMEZONE_IDENTIFIER
    * @return string the weeknumber as string
    */
    public final function getWeekNumber($timezone = TIMEZONE_IDENTIFIER)
    {
        return $this->getFormat('W', $timezone);
    }

    /**
    * Compares two Times with eachother. When the given parameter is before
    * the current Time object, then we return \System\Math\Math::COMPARE_LESSTHAN,
    * if it equals we return \System\Math\Math::COMPARE_EQUAL, else \System\Math\Math::COMPARE_GREATERTHAN.
    * @param \System\Calendar\Time A Time object to compare with.
    * @return int An integer representing the equality.
    */
    public final function compare(\System\Calendar\Time $time)
    {
        if ($this->toUNIX() == $time->toUNIX())
        {
            return \System\Math\Math::COMPARE_EQUAL;
        }
        return ($this->toUNIX() > $time->toUNIX()) ? \System\Math\Math::COMPARE_LESSTHAN : \System\Math\Math::COMPARE_GREATERTHAN;
    }

    /**
    * Subtract a number of years from the current timestamp
    * @param int the amount of years to subtract
    * @return Time The current object
    */
    public final function substractYear($year = 1)
    {
    	return $this->subtractYear($year);
	}

    /**
    * Subtract a number of years from the current timestamp
    * @param int the amount of years to subtract
    * @return Time The current object
    */
    public final function subtractYear($year = 1)
    {
		$this->timestamp = strtotime('-' . $year . ' year', $this->timestamp);
		return $this;
	}

	/**
    * Subtract a number of months from the current timestamp
    * @param int the amount of months to subtract
    * @return Time The current object
    */
	public final function substractMonth($month = 1)
	{
		return $this->subtractMonth($month);
	}

	/**
    * Subtract a number of months from the current timestamp
    * @param int the amount of months to subtract
    * @return Time The current object
    */
	public final function subtractMonth($month = 1)
	{
		$this->timestamp = strtotime('-' . $month . ' month', $this->timestamp);
		return $this;
	}

	/**
    * Subtract a number of days from the current timestamp
    * @param int the amount of days to subtract
    * @return Time The current object
    */
	public final function substractDay($day = 1)
	{
		return $this->subtractDay($day);
	}

	/**
    * Subtract a number of days from the current timestamp
    * @param int the amount of days to subtract
    * @return Time The current object
    */
	public final function subtractDay($day = 1)
    {
		$this->timestamp = strtotime('-' . $day . ' day', $this->timestamp);
		return $this;
	}

	/**
    * Subtract a number of hours from the current timestamp
    * @param int the amount of hours to subtract
    * @return Time The current object
    */
	public final function substractHour($hour = 1)
	{
		return $this->subtractHour($hour);
	}

	/**
    * Subtract a number of hours from the current timestamp
    * @param int the amount of hours to subtract
    * @return Time The current object
    */
	public final function subtractHour($hour = 1)
	{
		$this->timestamp = strtotime('-' . $hour . ' hour', $this->timestamp);
		return $this;
	}

	/**
    * Subtract a number of minutes from the current timestamp
    * @param int the amount of minutes to subtract
    * @return Time The current object
    */
	public final function substractMinute($minute = 1)
	{
		return $this->subtractMinute($minute);
	}

	/**
    * Subtract a number of minutes from the current timestamp
    * @param int the amount of minutes to subtract
    * @return Time The current object
    */
	public final function subtractMinute($minute = 1)
	{
		$this->timestamp = strtotime('-' . $minute . ' minute', $this->timestamp);
		return $this;
	}

	/**
    * Subtract a number of seconds from the current timestamp
    * @param int the amount of seconds to subtract
    * @return Time The current object
    */
	public final function substractSecond($second = 1)
	{
		return $this->subtractSecond($second);
	}

	/**
    * Subtract a number of seconds from the current timestamp
    * @param int the amount of seconds to subtract
    * @return Time The current object
    */
	public final function subtractSecond($second = 1)
	{
		$this->timestamp = strtotime('-' . $second . ' second', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of years from the current timestamp
    * @param int the amount of years to add
    * @return Time The current object
    */
	public final function addYear($year = 1)
	{
		$this->timestamp = strtotime('+' . $year . ' year', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of months from the current timestamp
    * @param int the amount of months to add
    * @return Time The current object
    */
	public final function addMonth($month = 1)
	{
		$this->timestamp = strtotime('+' . $month . ' month', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of days from the current timestamp
    * @param int the amount of days to add
    * @return Time The current object
    */
	public final function addDay($day = 1)
    {
		$this->timestamp = strtotime('+' . $day . ' day', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of hours from the current timestamp
    * @param int the amount of hours to add
    * @return Time The current object
    */
	public final function addHour($hour = 1)
	{
		$this->timestamp = strtotime('+' . $hour . ' hour', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of minutes from the current timestamp
    * @param int the amount of minutes to add
    * @return Time The current object
    */
	public final function addMinute($minute = 1)
	{
		$this->timestamp = strtotime('+' . $minute . ' minute', $this->timestamp);
		return $this;
	}

	/**
    * Add a number of seconds from the current timestamp
    * @param int the amount of seconds to add
    * @return Time The current object
    */
	public final function addSecond($second = 1)
	{
		$this->timestamp = strtotime('+' . $second . ' second', $this->timestamp);
		return $this;
	}

    /**
    * Creates a new time object based on the given MySQL timestamp.
    * @param string The MySQL timestamp
    * @return \System\Calendar\Time The timeobject
    */
    public static final function fromMySQLTimestamp($timestamp)
    {
        return new \System\Calendar\Time(strtotime($timestamp) ?: self::TIME_UNDEFINED);
    }

	/**
	* Returns the current time according to the server time.
	* @return string The current time in SQL format
	*/
    public static final function now()
    {
    	return date('Y-m-d H:i:s');
	}

	/**
	* Returns true if the time is equal to the epoch
	* @return bool True if the epoch, false otherwise
	*/
	public function isEpoch()
	{
		return $this->timestamp == 0;
	}
}
