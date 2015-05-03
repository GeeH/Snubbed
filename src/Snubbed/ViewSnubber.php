<?php
/**
 * Created by Gary Hockin.
 * Date: 30/04/15
 * @GeeH
 */

namespace Snubbed;


use Zend\Mvc\Application;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\AggregateResolver;

class ViewSnubber extends InjectTemplateListener
{

    /**
     * @var Application
     */
    private $application;
    /**
     * @var FileWriter
     */
    private $fileWriter;
    /**
     * @var array
     */
    private $paths = [];
    /**
     * @var ControllerManager
     */
    private $controllerManager;
    /**
     * @var array
     */
    private $controllers = [];
    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param Application $application
     */
    public function __construct(Application $application, FileWriter $fileWriter)
    {
        $this->application = $application;
        $this->fileWriter  = $fileWriter;
        $this->init();
    }

    /**
     * @param Application $application
     */
    function generateViewSnubs(Application $application)
    {

        /** @var AggregateResolver $viewResolver */
        $viewResolver = $application->getServiceManager()->get('view-resolver');

        $controllers = $this->getControllersFromManager();

        foreach ($controllers as $controller => $actions) {
            $this->handleActions($controller, $actions, $viewResolver);
        }

        $this->createPaths();
        $this->createHelpers();


    }

    /**
     * @param $controller
     * @param array $actions
     * @param AggregateResolver $viewResolver
     */
    function handleActions($controller, array $actions, AggregateResolver $viewResolver)
    {
        foreach ($actions as $action) {
            $template = $this->resolveTemplate($controller, $action);
            $resolved = $viewResolver->resolve($template);
            $this->addPath($controller, $action, $resolved);
        }
    }

    /**
     * @return array
     */
    function getControllersFromManager()
    {
        $return = [];
        foreach ($this->controllerManager->getCanonicalNames() as $controller => $normalised) {
            $return[$controller] = $this->getControllerMethods($controller);
        }
        return $return;
    }

    /**
     * @param $controller
     * @return array
     */
    function getControllerMethods($controller)
    {
        $this->controllers[$controller] = $this->controllerManager->get($controller);
        $methods                        = get_class_methods(get_class($this->controllers[$controller]));
        $methods                        = array_filter($methods, function ($action) {
            return fnmatch('*Action', $action);
        });
        return $methods;
    }

    /**
     * @param $controller
     * @param $action
     * @return false|string
     */
    private function resolveTemplate($controller, $action)
    {
        $template = $this->mapController($controller);
        if (!$template) {
            $namespace = substr($controller, 0, strrpos($controller, '\\'));
            $module    = $this->deriveModuleNamespace($controller);

            $controllerSubNs = $this->deriveControllerSubNamespace($namespace);
            if (!empty($controllerSubNs)) {
                if (!empty($module)) {
                    $module .= '/' . $controllerSubNs;
                } else {
                    $module = $controllerSubNs;
                }
            }

            $controller = $this->deriveControllerClass($controller);
            $template   = $this->inflectName($module);
            if (!empty($template)) {
                $template .= '/';
            }
            $template .= $this->inflectName($controller);
        }

        if (null !== $action) {
            $template .= '/' . $this->inflectName(str_replace('Action', '', $action));
        }

        return $template;
    }

    /**
     * @param $controller
     * @param $action
     * @param $resolved
     */
    private function addPath($controller, $action, $resolved)
    {
        if ($resolved) {
            $this->paths[$controller][$action] = $resolved;
        }
    }

    /**
     * Iterate over the paths
     */
    private function createPaths()
    {
        foreach ($this->paths as $controllerName => $controller) {
            $this->createPath($controllerName, $controller);
        }
    }

    /**
     * @param $controllerName
     * @param array $controller
     */
    private function createPath($controllerName, array $controller)
    {
        foreach ($controller as $action => $template) {
            $controllerClass = $this->controllers[$controllerName];
            try {
                $viewModel = $controllerClass->$action();
            } catch (\Exception $e) {
                $viewModel = null;
            }
            $this->createPathFromViewModel($viewModel, $controllerName, $action);
        }
    }

    /**
     * Set the controller manager - get from the service manager
     */
    private function init()
    {
        $this->controllerManager = $this->application->getServiceManager()->get('controller-manager');
    }

    /**
     * @param $viewModel
     * @param $controllerName
     * @param $action
     * @return bool
     */
    private function createPathFromViewModel($viewModel, $controllerName, $action)
    {
        if (!($viewModel instanceof ViewModel)) {
            return false;
        }

        /* @var ViewModel $viewModel */
        $variables = $viewModel->getVariables();

        $variablesArray = [];
        foreach ($variables as $name => $value) {
            $type = $this->getType($value);
            $variablesArray[$name] = $type;
        }

        $this->variables[$controllerName][$action] = $variablesArray;
    }

    /**
     * @param $value
     * @return string
     */
    private function getType($value)
    {
        $type = gettype($value);
        if (is_object($value)) {
            $type = get_class($value);
        }

        return $type;
    }

    /**
     *
     */
    private function createHelpers()
    {
        /** @var HelperPluginManager $viewPluginManager */
        $viewPluginManager = $this->application->getServiceManager()->get('view-helper-manager');
        $this->viewHelpers[] = $viewPluginManager;
    }
}
