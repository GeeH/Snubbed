<?php
/**
 * Created by Gary Hockin.
 * Date: 30/04/15
 * @GeeH
 */

namespace Snubbed;


use Zend\Form\Form;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
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
     * @var array
     */
    private $viewHelpers = [];

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
     * Generate the view snubs!
     */
    public function generateViewSnubs()
    {

        /** @var AggregateResolver $viewResolver */
        $viewResolver = $this->application->getServiceManager()->get('view-resolver');

        $controllers = $this->getControllersFromManager();

        foreach ($controllers as $controller => $actions) {
            $this->handleActions($controller, $actions, $viewResolver);
        }

        $this->createPaths();
        $this->createHelpers();

        ViewSnubGenerator::writeFiles($this->fileWriter, $this->paths, $this->controllers, $this->variables, $this->viewHelpers);
    }

    /**
     * @param $controller
     * @param array $actions
     * @param AggregateResolver $viewResolver
     */
    private function handleActions($controller, array $actions, AggregateResolver $viewResolver)
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
    private function getControllersFromManager()
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
    private function getControllerMethods($controller)
    {
        try {
            $this->controllers[$controller] = $this->controllerManager->get($controller);
            $methods                        = get_class_methods(get_class($this->controllers[$controller]));
            $methods                        = array_filter($methods, function ($action) {
                return fnmatch('*Action', $action);
            });
            return $methods;
        } catch (\Exception $e) {
            return [];
        }
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
            try {

                /** @var AbstractActionController $controllerClass */
                $controllerClass = $this->controllerManager->get($controllerName);
                $controllerClass->setEvent($this->application->getMvcEvent());

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
            $type                  = $this->getType($value);
            $variablesArray[$name] = $type;
        }

        $this->variables[$controllerName][$action] = $variablesArray;
    }

    /**
     * @param $value
     * @return string
     */
    private function getType($value, $inferType = true, $iterator = false)
    {
        if(is_a($value, Form::class)) {
            return get_class($value);
        }

        if(is_array($value) && !empty($value)) {
            $type = $value[0];
            return $this->getType($type, false, true);
        }

        if (is_a($value, \Iterator::class) && !empty($value)) {
            $type = $value->current();
            return $this->getType($type, false, true);
        }

        if (is_a($value, \IteratorAggregate::class) && $value->getIterator()->count() > 0) {
            $type = $value->getIterator()->current();
            return $this->getType($type, false, true);
        }

        if ($inferType) {
            $type = gettype($value);
            return $this->getType($type, false, $iterator);
        }

        if (is_object($value) && $iterator) {
            $type = get_class($value) . '[]';
            return $type;
        }
        if (is_object($value)) {
            $type = get_class($value);
            return $type;
        }

        return $value;

    }

    /**
     *
     */
    private function createHelpers()
    {
        /** @var HelperPluginManager $viewPluginManager */
        $viewPluginManager = $this->application->getServiceManager()->get('view-helper-manager');
        foreach ($viewPluginManager->getCanonicalNames() as $name) {
            $helper                   = $viewPluginManager->get($name);
            $this->viewHelpers[$name] = get_class($helper);
        }
    }
}
