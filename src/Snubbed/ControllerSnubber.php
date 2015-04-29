<?php
/**
 * Created by Gary Hockin.
 * Date: 29/04/15
 * @GeeH
 */

namespace Snubbed;


use Zend\Mvc\Application;

class ControllerSnubber
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
        $this->fileWriter = $fileWriter;
    }

    /**
     * @param string $abstractController
     */
    public function generateControllerStub($abstractController)
    {
        /** @var \Zend\Mvc\Controller\PluginManager $controllerPluginManager */
        $controllerPluginManager = $this->application->getServiceManager()->get('controller-plugin-manager');

        $methods = [];

        foreach ($controllerPluginManager->getCanonicalNames() as $key => $name) {
            $pluginClass   = $controllerPluginManager->get($name);
            $methods[$key] = get_class($pluginClass);
        }

        $className = str_replace('\\', '', $abstractController);
        $generated = date("Y-m-d H:i:s");

        $snub = <<<SNUB
<?php

/**
 * Generated on {$generated}
 */
namespace Snubbed;

/**

SNUB;

        foreach ($methods as $name => $returns) {
            if (strpos($returns, '\\') !== 0) {
                $returns = '\\' . $returns;
            }
            $snub .= " * @method $returns $name()" . PHP_EOL;
        }

        $snub .= <<<SNUB
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @property \Zend\Http\PhpEnvironment\Request request
 * @method \Zend\Http\PhpEnvironment\Response getResponse()
 * @property \Zend\Http\PhpEnvironment\Response response
 */
abstract class Snubbed$className extends $abstractController {}


SNUB;

        $this->fileWriter->write('.ide/Snubbed/Snubbed' . $className . '.php', $snub);
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }
}
