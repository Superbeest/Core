<?php
/**
* Controller.class.php
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


namespace System\Web;

if (!defined('System'))
{
    die ('Hacking attempt');
}

/**
* The base Controller implementation
* @package \System\Web
*/
abstract class Controller extends \System\Base\BaseObj
{
    /**
    * The renderer is enabled and will be used.
    */
    const RENDERER_ENABLED = 1;
    /**
    * The renderer is disabled and no output functionality is applied. This is usefull for
    * incremental output like for scripts or console output passing.
    */
    const RENDERER_DISABLED = 0;

	/**
	* The key used to check for cookie consent
	*/
    const COOKIE_CONSENT_KEY = 'consent';

    /**
    * @var integer The current setting for the renderer. Default we render everything using our own renderers.
    */
    private $renderMode = self::RENDERER_ENABLED;

	/**
	* @var \System\Register\Register The registry
	*/
    private $register = null;

    /**
    * @var \System\Collection\Map The results of the services
    */
    private $serviceResults = null;

    /**
    * @var \System\Output\RenderSurface The surface to render the buffer on to.
    */
    private $renderSurface = null;

	/**
	* @publicset
	* @publicget
	* @var bool True if the user consented to the use of cookies
	*/
    protected $cookieConsent = false;

    /**
    * This function is the default entry for a controller. When a controller is called without any parameters,
    * then the control is transferred to the defaultAction function.
    * The primary entrypoint must be public.
    */
    public abstract function defaultAction();

    /**
    * This function is called in the beginning of the controller, before the execution of the specified
    * requesthandler (for example: defaultAction()). It is used to register request services that
    * can process the users input.
    * All the given services will be processed once each call, allowing for successive service calls.
    * @param \System\Collection\Vector The vector with all the services to call.
    */
    protected abstract function deployServices(\System\Collection\Vector $services);

    /**
    * The constructor for controllers. This method cannot be overriden.
    */
    public final function __construct()
    {
        $this->register = \System\Register\Register::getInstance();
    }

    /*
    * Gets the Register object
    * @return \System\Register\Register The Register object
    */
    public function getRegister()
    {
    	return $this->register;
	}

    /**
    * Returns the current setting for the renderer.
    * Also see \System\Web\Controller::RENDERER_ENABLED and \System\Web\Controller::RENDERER_DISABLED.
    * This function is called by the system to determine whether or not to render the output using the surfacing system.
    * @return integer The render setting.
    */
    public final function getRenderSetting()
    {
        return $this->renderMode;
    }

    /**
    * Sets the new rendermode. This function switches the current RenderMode.
    * @param integer Can only be \System\Web\Controller::RENDERER_ENABLED or \System\Web\Controller::RENDERER_DISABLED.
    */
    protected final function setRenderMode($renderMode = \System\Web\Controller::RENDERER_ENABLED)
    {
        if (($renderMode == \System\Web\Controller::RENDERER_ENABLED) ||
            ($renderMode == \System\Web\Controller::RENDERER_DISABLED))
        {
            $this->renderMode = $renderMode;
        }
    }

    /**
    * Returns the current default Database connection. The default Database connection is the primary connection as defined
    * in the configuration file.
    * @return \System\Db\Database The current active default Database object
    */
    protected final function getDefaultDb()
    {
		assert($this->register->defaultDb instanceof \System\Db\Database);
        return $this->register->defaultDb;
    }

    /**
    * Attaches the given RenderSurface to the Controller for output.
    * @param \System\Output\RenderSurface The RenderSurface to attach to the controller
    */
    protected final function setRenderSurface(\System\Output\RenderSurface $renderSurface)
    {
        $this->renderSurface = $renderSurface;
    }

    /**
    * Returns the current RenderSurface for the controller.
    * This function will be called automatically after the controller finishes.
    * @return \System\Output\RenderSurface The RenderSurface to render
    */
    public final function getRenderSurface()
    {
        return $this->renderSurface;
    }

    /**
    * This function retrieves the serviceresult map. The service is allowed to put data in this map.
    * The service name must be identical to the name used for registerring the service.
    * @param string The full name of the service.
    * @return mixed A \System\Collection\Map with the serviceresult, of false if there is no result
    */
    protected final function getServiceResult($service)
    {
        if ($this->hasServiceResult($service))
        {
            return $this->serviceResults[$service];
        }

        return false;
    }

    /**
    * This function indicates whether or not the service was completed succesfully and
    * produced as serviceResult.
    * The servicename must be identical to the name used for registerring the service.
    * @param string The full name of the service
    * @return bool True if the service completed, false otherwise.
    */
    protected final function hasServiceResult($service)
    {
        return isset($this->serviceResults[$service]);
    }

    /**
    * This function is the initiator of the controller call chain. It is called
    * automatically by the bootloader and spawns the proper controller child, based on the request.
    * When there is no request specified, the default controller will be loaded.
    * The BeforeControllerLoadEvent is called prior to the loading of the controller.
    * When the requested controller cannot be loaded, an OnControllerNotFoundEvent event will be fired and the optional replacement controller will be loaded.
    * @return \System\Web\Controller The current selected controller
    */
    public static final function callController()
    {
		$options = getopt('', array(
			'controller::',
			'action::',
			'mod::'
		));

        $get = new \System\HTTP\Request\Get();

        //we default to the given config namespace and the config controller.
        $targetNamespace = SITE_NAMESPACE;
        $targetController = DEFAULT_CONTROLLER;
        $targetAction = 'defaultAction';

        //The default controller and the default namespace can be overridden by parameters
        if (isset($get->controller))
        {
            $targetController = ucfirst($get->controller);
        }
        else if (isset($options['controller']))
        {
        	$targetController = ucfirst($options['controller']);
		}

        if (isset($get->action))
        {
            $targetAction = ucfirst($get->action);
        }
        else if (isset($options['action']))
        {
        	$targetAction = ucfirst($options['action']);
		}

        /*
        for security reasons, we only allow the overridden namespace to be executed from within the global modules.
        Effectively, this means that a mod override parameter is effectively transferring control to a global module
        instead of de default controllers. This way, we support controllers within global modules.
        */
        if (isset($get->mod))
        {
            $targetNamespace = (ucfirst($get->mod) == 'System' ? '\System\\' : '\Module\\') . ucfirst($get->mod);
        }
        else if (isset($options['mod']))
        {
        	$targetNamespace = (ucfirst($options['mod']) == 'System' ? '\System\\' : '\Module\\') . ucfirst($options['mod']);
		}

        //create the controller and run it
        $controllerName = $targetNamespace . '\\' . $targetController;

        $controller = null;
        if ((class_exists($controllerName, true)) &&
            (is_subclass_of($controllerName, '\System\Web\Controller')))
        {
            $controller = new $controllerName();
        }
        else
        {
            //try to load a replacement controller using eventcatching
            if (\System\Event\EventHandler::hasListeners('\System\Event\Event\OnControllerNotFoundEvent'))
            {
                $event = new \System\Event\Event\OnControllerNotFoundEvent();
                $event->setControllerRequest($controllerName);
                $event->setControllerReplacement($controllerName);
                $event->raise();

                $controllerReplacement = $event->getControllerReplacement();

                if (($controllerReplacement != null) &&
                    (class_exists($controllerReplacement, true)) &&
                    (is_subclass_of($controllerReplacement, '\System\Web\Controller')))
                {
                    $controller = new $controllerReplacement();
                }
            }
        }

        //check if the controller actually is a valid controller
        if ($controller instanceof \System\Web\Controller)
        {
			$requestMethod = \System\HTTP\Request\Request::getMethod();

			//throws before load events and checks if the controller needs to be replaced
			$controller = self::checkControllerReplacement($controller, $targetNamespace, $targetAction, $requestMethod);

			//process sapi specific calls
			self::proccessSAPICalls($controller);

            //process the services
            self::processServices($controller);

            //call the proper entry action, but also allow specific method based versions of that call
            //example: defaultAction_GET, defaultAction_POST, etc. This falls back to defaultAction
			switch (true)
			{
				case self::attemptControllerAction($controller, $targetAction . '_' . $requestMethod):
					$action = $targetAction . '_' . $requestMethod;
					break;
				case self::attemptControllerAction($controller, $targetAction):
					$action = $targetAction;
					break;
				case self::attemptControllerAction($controller, 'defaultAction_' . $requestMethod):
					$action = 'defaultAction_' . $requestMethod;
					break;
				default:
					$action = 'defaultAction';
					break;
			}

            $controller->$action();
            return $controller;
        }

        throw new \System\Error\Exception\InvalidControllerException('The given controller is invalid or does not exist: ' . $targetController . ' in ' . $targetNamespace);
    }

    /**
    * Throws load events of the controller and checks if the controller needs to be replaced by a different controller.
    * @param Controller The requested controller
    * @param string The requested namespace
    * @param string The requested action
    * @param string The requested method
    * @return Controller The controller to run
    */
    private static final function checkControllerReplacement(Controller $controller, $targetNamespace, $targetAction, $requestMethod)
    {
    	//if the cookie consent is not present, we throw this event
    	if ((\System\Server\SAPI::getSAPI() == \System\Server\SAPI::SAPI_BROWSER) &&
    		(self::checkCookieConsent()))
    	{
			//fire the load event
	        $event = new \System\Event\Event\OnBeforeControllerLoadNoCookieConsentEvent();
	        $event->setController($controller);
	        $event->setModuleName($targetNamespace);
	        $event->setActionName($targetAction);
	        $event->setMethod($requestMethod);
	        $event->raise();

	        //the OnBeforeControllerLoadEvent
	        $controllerReplacement = $event->getControllerReplacement();
	        if (($controllerReplacement != null) &&
	            (class_exists($controllerReplacement, true)) &&
	            (is_subclass_of($controllerReplacement, '\System\Web\Controller')))
	        {
	            return new $controllerReplacement();
	        }
		}

    	//fire the load event
        $event = new \System\Event\Event\OnBeforeControllerLoadEvent();
        $event->setController($controller);
        $event->setModuleName($targetNamespace);
        $event->setActionName($targetAction);
        $event->setMethod($requestMethod);
        $event->raise();

        //the OnBeforeControllerLoadEvent
        $controllerReplacement = $event->getControllerReplacement();
        if (($controllerReplacement != null) &&
            (class_exists($controllerReplacement, true)) &&
            (is_subclass_of($controllerReplacement, '\System\Web\Controller')))
        {
            return new $controllerReplacement();
        }

        return $controller;
	}

    /**
    * Process SAPI dependant calls
    * @param Controller The requested controller
    */
    private static final function proccessSAPICalls(\System\Web\Controller $controller)
    {
    	switch (\System\Server\SAPI::getSAPI())
			{
				case \System\Server\SAPI::SAPI_CLI:
					//in cli mode, we disable the renderer
					$controller->setRenderMode(\System\Web\Controller::RENDERER_DISABLED);
					break;
				case \System\Server\SAPI::SAPI_BROWSER:
				default:
					//process the cookie consent, but only in non-CLI mode
					$controller->setCookieConsent(self::checkCookieConsent());
					break;
			}
	}

	/**
	* Checks if the user sends a 'consent' request to the server.
	* If so, we set the consent cookie and process that.
	* @return bool True if there is cookie consent, false otherwise
	*/
    protected static final function checkCookieConsent()
    {
		$get = new \System\HTTP\Request\Get();
		$post = new \System\HTTP\Request\Post();
		$cookie = new \System\HTTP\Storage\Cookie();
		$key = self::COOKIE_CONSENT_KEY;

		//check if the consent parameter is set in the post
		if ((isset($post->$key)) &&
			(ctype_digit($post->$key)))
		{
			if ($post->$key == 1)
			{
				$cookie->$key = 1;
			}
		}
		//check if the consent parameter is set in the get
		if ((isset($get->$key)) &&
			(ctype_digit($get->$key)))
		{
			if ($get->$key == 1)
			{
				$cookie->$key = 1;
			}
		}

		//do the cookie check
		if (isset($cookie->$key))
		{
			return $cookie->$key == 1;
		}

		return false;
	}

	/**
	* Check for the existance of the given target action and check its reflection states
	* @param Controller The requested controller
	* @param string The action to check for
	* @return bool True if exists and is a valid callback, false otherwise
	*/
    private static final function attemptControllerAction(\System\Web\Controller $controller, $targetAction)
    {
    	if ((method_exists($controller, $targetAction)) &&
            (is_callable(array($controller, $targetAction))))
        {
			//we only call public methods who are not static.
			//this effectively makes all public methods in a controller callable
            $rm = new \ReflectionMethod($controller, $targetAction);
            if (($rm->isPublic()) &&
            	(!$rm->isStatic()))
            {
            	return true;
			}
		}

		return false;
	}

    /**
    * This function retrieves all the services for the controller and then executes them.
    * @param \System\Web\Controller The controller to process the services for.
    */
    private static final function processServices(\System\Web\Controller $controller)
    {
        //get all the registered services for this controller
        $services = new \System\Collection\Vector();
        $controller->deployServices($services);

        $serviceResults = new \System\Collection\Map();
        //process all the registered services
        foreach ($services as $service)
        {
            if ($service instanceof \System\Web\ServiceEntry)
            {
                //this is an optimization. A ServiceEntry needs to define on which actionName it needs to fire.
                //for instance: <actioncontainer>-><actionfield> must be a specific <actionname> to fire the service.
                //the service sets the value to check for, so the servicemanagement can distinguish if it is needed or not
                switch ($service->getActionType())
                {
                    case \System\Web\ServiceEntry::ACTIONTYPE_POST:
                        $actionContainer = new \System\HTTP\Request\Post();
                        break;
                    case \System\Web\ServiceEntry::ACTIONTYPE_GET:
                        $actionContainer = new \System\HTTP\Request\Get();
                        break;
                    case \System\Web\ServiceEntry::ACTIONTYPE_SESSION:
                        $actionContainer = new \System\HTTP\Storage\Session();
                        break;
                    case \System\Web\ServiceEntry::ACTIONTYPE_COOKIE:
                        $actionContainer = new \System\HTTP\Storage\Cookie();
                        break;
                    default:
                        throw new \InvalidArgumentException('Unknown ACTIONTYPE given. Please check the serviceentries');
                }

                //do the actual matching and check if we need to fire this service call
                $actionType = $service->getActionField();
                $actionValue = (string)$actionContainer->$actionType;
                if ($actionValue == $service->getActionName())
                {
                    if (is_callable($service->getCallback()))
                    {
                        $serviceResult = new \System\Collection\Map();

						//automatically call the validation of handles
                        \System\Web\Service::validateHandles();

                        //passing parameters to a static method implies call by reference. thus the params are passed by reference
                        $isMatch = call_user_func_array($service->getCallback(), array($serviceResult, $controller->getDefaultDb()));
                        if ((is_bool($isMatch)) &&
                            ($isMatch))
                        {
                            $serviceResults[$service->getCallback()] = $serviceResult;

                            //we raise the proper event
                            $event = new \System\Event\Event\OnServiceExecutedEvent();
                            $callBack = $service->getCallback();
                            $event->setServiceCallback($callBack);
                            $event->setServiceResult($serviceResult);
                            $event->raise($controller);
                        }
                    }
                    else
                    {
                        throw new \InvalidArgumentException('Cannot execute the callback in the given service. Please check the callback');
                    }
                }
            }
            else
            {
                throw new \InvalidArgumentException('The given entry in the \'deployServices\' method is not a \System\Web\ServiceEntry.');
            }
        }

        //we store the service results in the controller
        $controller->setServiceResults($serviceResults);
    }

    /**
    * Sets the service results to the specific controller
    * @param \System\Collection\Map The serviceresults
    */
    private final function setServiceResults(\System\Collection\Map $serviceResults)
    {
        $this->serviceResults = $serviceResults;
    }
}
