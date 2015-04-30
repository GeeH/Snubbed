<?php
/**
 * Created by Gary Hockin.
 * Date: 30/04/15
 * @GeeH
 */

namespace Snubbed;


use Zend\Mvc\View\Http\InjectTemplateListener;

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
     * @param Application $application
     */
    public function __construct(Application $application, FileWriter $fileWriter)
    {
        $this->application = $application;
        $this->fileWriter  = $fileWriter;
    }


    function generateViewSnubs(Application $application)
    {

        $myListener = new MyInjectTemplateListener();

        /** @var \Zend\View\Resolver\AggregateResolver $viewResolved */
        $viewResolver = $application->getServiceManager()->get('view-resolver');

        $controllers = getControllersFromManager($application->getServiceManager()->get('controller-manager'));
        $result      = [];
        foreach ($controllers as $controller => $actions) {
            $result = array_merge(handleActions($controller, $actions, $viewResolver, $myListener), $result);
        }

        var_dump($result); die();
//        return $result;


    }

    function handleActions($controller, array $actions, AggregateResolver $viewResolver, MyInjectTemplateListener $templateListener)
    {
        $return = [];
        foreach ($actions as $action) {
            var_dump($controller, $action);
            $template                     = $templateListener->resolveTemplate($controller, $action);
            $resolved                     = $viewResolver->resolve($template);
            $return[$controller][$action] = $resolved;
        }

        return $return;
    }


    function getControllersFromManager(ControllerManager $controllerManager)
    {
        $return = [];
        var_dump($controllerManager->getCanonicalNames());
        die();
        foreach ($controllerManager->getRegisteredServices() as $type => $controllers) {
            $return = getControllers($controllers, $controllerManager, $return);
        }
        return $return;
    }

    function getControllers(array $controllers, ControllerManager $controllerManager, $return)
    {
        foreach ($controllers as $controller) {
            $methods             = get_class_methods(get_class($controllerManager->get($controller)));
            $methods             = array_filter($methods, function ($action) {
                return fnmatch('*Action', $action);
            });
            $return[$controller] = $methods;
        }
        return $return;
    }

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
            $template .= '/' . $this->inflectName($action);
        }

        return $template;
    }
}
