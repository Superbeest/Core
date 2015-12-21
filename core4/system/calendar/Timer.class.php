<?php
/**
* Timer.class.php
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
* The Timer class contains functionality to time specific actions and
* monitor duration.
* @package \System\Calendar
*/
class Timer extends \System\Base\Base
{
    /**
    * @var \System\Calendar\Timer The total system execution time. It is measured by its own timer
    */
    private static $systemTimer = null;

    /**
    * The internal clock
    */
    protected $timer = 0;

    /**
    * Start the timing
    */
    public final function start()
    {
        $this->timer = microtime(true);
    }

    /**
    * Stop the timing and calculate the difference between the start and the stop periods.
    * The start() function needs to be called before using this function.
    */
    public final function stop()
    {
        //the timer should have started
        assert($this->timer != 0);

        $this->timer = microtime(true) - $this->timer;
    }

    /**
    * Gets the time between the start() and stop() calls. Note that
    * we use microseconds for precision, and note that because of the
    * multitasking nature of PHP, the timing may be a few ms off.
    * The returnformat is 2 digits and 4 decimals.
    * @return string Returns a string with the duration.
    */
    public final function getDuration()
    {
        return sprintf('%01.4f', $this->timer);
    }

    /**
    * Gets the current duration of the timer, without stopping the timer.
    * Gets the time between the start() call and this call. Note that
    * we use microseconds for precision, and note that because of the
    * multitasking nature of PHP, the timing may be a few ms off.
    * The returnformat is 2 digits and 4 decimals.
    * @return string Returns a string with the duration.
    */
    public final function getCurrentDuration()
    {
        //the timer should have started
        assert($this->timer != 0);

        return sprintf('%01.4f', (microtime(true) - $this->timer));
    }

    /**
    * Returns the current execution time of the system.
    * This value keeps incrementing during the execution.
    * @return string The amount of execution time.
    */
    public static final function getSystemExecutionTime()
    {
        if (self::$systemTimer == null)
        {
            self::$systemTimer = new \System\Calendar\Timer();
            self::$systemTimer->start();
        }

        $time = self::$systemTimer->getCurrentDuration();

        $event = new \System\Event\Event\OnSystemExecutionTimeRequestEvent();
        $event->setTimer(self::$systemTimer);
        $event->raise();

        return $time;
    }
}
