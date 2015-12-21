<?php
/**
* EventHandler.class.php
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


namespace System\Event;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* This class contains functionality to support event handling in our system
* @package \System\Event
*/
abstract class EventHandler extends \System\Base\BaseObj
{
    /**
    * The container for all the events
    */
    private static $events = null;

    /**
    * @var mixed The sender of the event
    */
    private $sender = null;

    /**
    * Gets and validates a handle
    * @return \System\Collection\Map The collection with the events
    */
    private static final function validateHandle()
    {
        if (self::$events == null)
        {
            self::$events = new \System\Collection\Map();
        }

        return self::$events;
    }

    /**
    * Registers a callback to a given Event. Whenever the event is raised, the callback gets executed.
    * @param string The name of the event, or can be omitted to auto retrieve based on the current context
    * @param callback The callback to execute upon raising the event.
    */
    public static final function register(...$params)
    {
		if (count($params) == 1)
		{
			array_unshift($params, self::getStaticClassName());
		}
		list($eventName, $callback) = $params;

        $handle = self::validateHandle();
        $key = self::getRegistrationKey($eventName);
        if (!isset($handle->$key))
        {
            $handle->$key = new \System\Collection\Vector();
        }

        if (!$handle->$key->contains($callback))
        {
            $handle->$key->add($callback);
        }
    }

    /**
    * Creates a key from the given eventname
    * @param string The name of the event
    * @return string The name of the key
    */
    private static final function getRegistrationKey($eventName)
    {
        $instance = null;

        if (class_exists($eventName))
        {
            $instance = new $eventName;
        }
        else
        {
            throw new \InvalidArgumentException('The given parameter does not seem to be accessible: ' . $eventName);
        }

        if (is_subclass_of($instance, '\System\Event\EventHandler'))
        {
            $key = get_class($instance);
        }
        else
        {
            throw new \InvalidArgumentException('The given parameter does not seem to be an event: ' . $eventName);
        }

        $key = $eventName;

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        return $key;
    }

    /**
    * Unregisters a callback corresponding to the given event.
    * @param string The name of the event.
    * @param callback The callback to detach from the event
    */
    public static final function unregister($eventName, $callback)
    {
        $handle = self::validateHandle();
        $key = self::getRegistrationKey($eventName);

        if ((isset($handle->$key)) &&
            ($handle->$key->contains($callback)))
        {
            unset($handle->$key->$callback);
        }
    }

    /**
    * Returns whether or not there are listeners registered to the event
    * @param string The name of the event to check for
    * @return boolean True when there are listeners, false otherwise.
    */
    public static final function hasListeners($eventName)
    {
        $handle = self::validateHandle();
        $key = self::getRegistrationKey($eventName);

        if (isset($handle->$key))
        {
            return $handle->$key->count() > 0;
        }

        return false;
    }

    /**
    * This function will be called automatically if the current exception is raised.
    * You are allowed to manually raise an event.
    * @param mixed The sender of the object, defaults to null
    */
    public final function raise($sender = null)
    {
        $handle = self::validateHandle();

        //set the sender
        $this->setSender($sender);

        $key = get_class($this);

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        if ((isset($handle->$key)) &&
            ($handle->$key instanceof \System\Collection\Vector))
        {
            foreach ($handle->$key as $callback)
            {
                if (is_callable($callback))
                {
                    call_user_func_array($callback, array($this));
                }
                else
                {
                    throw new \System\Error\Exception\SystemException('The given callback is not valid for the current event: ' . get_class($this));
                }
            }
        }
    }

    /**
    * Gets the sender object. Usually when an event is raised, it
    * sets the sender of the object, but it may be a null reference
    * @return mixed The sender of the event
    */
    public final function getSender()
    {
        return $this->sender;
    }

    /**
    * Sets the sender of the object. This usually is the caller of the event, but may
    * be a null reference.
    * @param mixed The sender
    */
    private final function setSender($sender = null)
    {
        $this->sender = $sender;
    }

    /**
    * Force an empty constructor
    */
    public function __construct()
    {
	}
}
