<?php
/**
* Calendar.class.php
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
* The calendar class contains helper functions to manipulate time objects
* Definition: monday is the first day of the week.
* @package \System\Calendar
*/
class Calendar extends \System\Base\StaticBase
{
    /**
    * Gets the current time. This is based on the serverconfiguration and might or might not
    * support winter/summer time.
    * @return \System\Calendar\Time A Time object representing the current time
    */
    public static final function getCurrentTime()
    {
        return new \System\Calendar\Time(time());
    }

    /**
    * Gets a new date, based on the given parameter with added or subtracted amount of days.
    * @param \System\Calendar\Time the time to use as a base
    * @param integer The difference in days from the given time.
    * @return \System\Calendar\Time A new Time object based on the given date and the given difference
    */
    public static final function getTimeDiffInDays(\System\Calendar\Time $time, $differenceInDays)
    {
        return new \System\Calendar\Time(strtotime('@' . $time->toUNIX() . ' +' . $differenceInDays . 'day'));
    }

    /**
    * Calculates the amount of days in the given month.
    * @param \System\Calendar\Time a time object to calculate the amount of days in the current month from.
    * @return int The amount of days in the given month/
    */
    public static final function getAmountOfDaysInMonth(\System\Calendar\Time $time)
    {
        return cal_days_in_month(CAL_GREGORIAN, $time->getMonth(), $time->getYear());
    }

    /**
    * Gets the first weekday from the given week.
    * @param \System\Calendar\Time The time to create the first weekday from
    * @return \System\Calendar\Time A Time object for the first day of the week
    */
    public static final function getFirstWeekdayFromTime(\System\Calendar\Time $time)
    {
        $day = $time->getDayOfWeek();

        //current day to the first day of the week
        return self::getTimeDiffInDays($time, ($day - 1));
    }

    /**
    * Gets all the days in the current month as Time objects
    * @param \System\Calendar\Time The month to get the days from
    * @return \System\Collection\Vector A Vector with all the days in the given month
    */
    public static final function getDaysInMonth(\System\Calendar\Time $time)
    {
        $numberOfDays = self::getAmountOfDaysInMonth($time);

        $days = new \System\Collection\Vector();

        for ($day = 1; $day <= $numberOfDays; $day++)
        {
            $days[] = new \System\Calendar\Time($time->getYear(), $time->getMonth(), $day);
        }

        return $days;
    }

    /**
    * Gets all the days in the current week as Time objects
    * @param \System\Calendar\Time The week to get the days from
    * @return \System\Collection\Vector A Vector with all the days in the given week
    */
    public static final function getDaysInWeek(\System\Calendar\Time $time)
    {
        $monday = self::getFirstWeekdayFromTime($time);

        $days = new \System\Collection\Vector();
        for ($d = 0; $d < \System\Calendar\Time::DAYS_IN_WEEK; $d++)
        {
            $days[] = self::getTimeDiffInDays($monday, $d);
        }

        return $days;
    }

    /**
    * Returns the amount of days until the next birthday.
    * @param \System\Calendar\Time The date of birth
    * @return int The amount of days
    */
    public static final function getDaysUntilBirthday(\System\Calendar\Time $birthday)
    {
        $thisYearBirthday = new \System\Calendar\Time(date('Y'), $birthday->getMonth(), $birthday->getDay());
        $nextYearBirthday = new \System\Calendar\Time(date('Y') + 1, $birthday->getMonth(), $birthday->getDay());

        $today = self::getCurrentTime();

        $countDown = $nextYearBirthday;
        if (($today->compare($thisYearBirthday) == \System\Math\Math::COMPARE_LESSTHAN) ||
            ($today->compare($thisYearBirthday) == \System\Math\Math::COMPARE_EQUAL))
        {
            $countDown = $thisYearBirthday;
        }

        $diff = $countDown->toUNIX() - $today->toUNIX();
        return ceil($diff / \System\Calendar\Time::SECONDS_IN_DAY);
    }

    /**
    * Returns the amount of years between to given Time objects. This only includes full years.
    * The order of the Time objects is not relevant
    * @param \System\Calendar\Time The first time
    * @param \System\Calendar\Time The second time
    * @return int The amount of years between the given Time objects
    */
    public static final function getAmountOfYearsBetweenTimes(\System\Calendar\Time $time1, \System\Calendar\Time $time2)
    {
        $diff = abs($time1->toUNIX() - $time2->toUNIX());

        return floor($diff / \System\Calendar\Time::SECONDS_IN_YEAR);
    }

    /**
    * Gets the age based on the given birthday and the current time. This only includes full years.
    * @param \System\Calendar\Time The birthday
    * @return int The amount of years between the current time and the given time
    */
    public static final function getAge(\System\Calendar\Time $birthday)
    {
        return self::getAmountOfYearsBetweenTimes($birthday, self::getCurrentTime());
    }

    /**
    * Returns the amount of days between two given times. The order of the Time objects is not relevant.
    * @param \System\Calendar\Time The first time
    * @param \System\Calendar\Time The second time
    * @return int The amount of days between the given Time objects
    */
    public static final function getAmountOfDaysBetweenTimes(\System\Calendar\Time $time1, \System\Calendar\Time $time2)
    {
        $diff = abs($time1->toUNIX() - $time2->toUNIX());

        return floor($diff / \System\Calendar\Time::SECONDS_IN_DAY);
    }
}
