<?php
/**
* Hook.class.php
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


namespace System\Inspection;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* Add functionality to use a semi AO like structure in the software.
* It is currently limited to PRE and POST hooks.
* Note that the order of execution for multiple hooks, is the order in which the hooks are added.
* @package \System\Inspection
*/
class Hook extends \System\Base\StaticBase
{
    /**
    * Defines the time to hook before the execution of the function.
    */
    const HOOK_PRE = 0;
    /**
    * Defines the time to hook after the execution of the function.
    */
    const HOOK_POST = 1;

    /**
    * The container for all the hooks
    */
    private static $hooks = null;

    /**
    * Gets and validates a handle
    * @return \System\Collection\Map The collection with the hooks
    */
    private static final function validateHandle()
    {
        if (self::$hooks == null)
        {
            self::$hooks = new \System\Collection\Map();
        }

        return self::$hooks;
    }

    /**
    * Adds a hook to a given callback at the given time. Every time the function gets executed using the call() function,
    * the system will check for any available hooks and executes them.
    * If the given hook already exists, it will not add it twice.
    * If the attachTo and the execute parameter are the same, you will generate a circular loop, causing stack overflows.
    * @param callback The callback to attach the hooks to. Note that this must be a public accessable function.
    * @param callback The hook to place. This function will be executed at the given time. It must be public.
    * @param integer The moment of execution. Use \System\Inspection\Hook::HOOK_PRE for pre-execution and \System\Inspection\Hook::HOOK_POST for post execution.
    */
    public static final function register($attachTo, $execute, $executeMoment = \System\Inspection\Hook::HOOK_POST)
    {
        $handle = self::validateHandle();

        $key = \System\Type::callbackToString($attachTo);

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        if (!$handle->keyExists($key))
        {
            $preHooks = new \System\Collection\Map();
            $postHooks = new \System\Collection\Map();
            $hooks = new \System\Collection\Map();
            $hooks->pre = $preHooks;
            $hooks->post = $postHooks;
            $handle->$key = $hooks;
        }

        switch ($executeMoment)
        {
            case \System\Inspection\Hook::HOOK_POST:
                $handle->$key->post[] = $execute;
                break;
            case \System\Inspection\Hook::HOOK_PRE:
                $handle->$key->pre[] = $execute;
                break;
            default:
                throw new \InvalidArgumentException('the given executemoment is invalid');
        }
    }

    /**
    * Detaches a hook from a given callback.
    * @param callback The callback to detach from
    * @param callback The hook that was executed
    * @param integer The moment of execution.
    */
    public static final function unregister($detachFrom, $execute, $executeMoment = \System\Inspection\Hook::HOOK_POST)
    {
        $handle = self::validateHandle();

        $key = \System\Type::callbackToString($detachFrom);

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        if ($handle->keyExists($key))
        {
            switch ($executeMoment)
            {
                case \System\Inspection\Hook::HOOK_POST:
                    foreach ($handle->$key->post as $index=>$post)
                    {
                        if ($post == $execute)
                        {
                            unset($handle->$key->post[$index]);
                            return;
                        }
                    }
                    break;
                case \System\Inspection\Hook::HOOK_PRE:
                    foreach ($handle->$key->pre as $index=>$pre)
                    {
                        if ($pre == $execute)
                        {
                            unset($handle->$key->pre[$index]);
                            return;
                        }
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('the given executemoment is invalid');
            }
        }
    }

    /**
    * Retrieves all the registered hooks for the given callback.
    * @param callback The callback to look for
    * @param integer The executemoment to return
    * @return \System\Collection\Map containing all the registered hooks
    */
    public static final function getHooks($callback, $executeMoment = \System\Inspection\Hook::HOOK_POST)
    {
        $handle = self::validateHandle();

        $key = \System\Type::callbackToString($callback);

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        if ($handle->keyExists($key))
        {
            switch ($executeMoment)
            {
                case \System\Inspection\Hook::HOOK_POST:
                    return $handle->$key->post;
                case \System\Inspection\Hook::HOOK_PRE:
                    return $handle->$key->pre;
                default:
                    throw new \InvalidArgumentException('the given executemoment is invalid');
            }
        }

        return new \System\Collection\Map();
    }

    /**
    * Executes a given callback and any hooks associated with it.
    * The execution will be:
    * The PRE execution hooks in order of adding.
    * The execute callback
    * The POST execution hooks in order of adding.
    * Please note that this function will only return the returnvalue of the given execute function.
    * Also not that this function does work recursively; the hooks themselves are called using the call function.
    * The hooks are called using the same parameters as the execute function.
    * The hooks may or may not have their own returnvalues, but these will be ignored.
    * @param callback The callback to execute.
    * @param array The arguments to pass to the execute function
    */
    public static final function call($execute, array $arguments = array())
    {
        $handle = self::validateHandle();

        $key = \System\Type::callbackToString($execute);

        if (mb_substr($key, 0, 1) == '\\')
        {
            $key = mb_substr($key, 1);
        }

        //pre hooks
        if ($handle->keyExists($key))
        {
            foreach ($handle->$key->pre as $preHook)
            {
                if (is_callable($preHook))
                {
                    self::call($preHook, $arguments);
                }
            }
        }

        $returnValue = call_user_func_array($execute, $arguments);

        //post hooks
        if ($handle->keyExists($key))
        {
            foreach ($handle->$key->post as $postHook)
            {
                if (is_callable($postHook))
                {
                    //add the return value to the parameters
                    array_push($arguments, $returnValue);
                    self::call($postHook, $arguments);
                }
            }
        }

        return $returnValue;
    }
}
