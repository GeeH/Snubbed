<?php
/**
 * Created by Gary Hockin.
 * Date: 29/04/15
 * @GeeH
 */

namespace SnubbedTest;


use Snubbed\ControllerSnubber;
use Snubbed\FileWriter;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;

class ControllerSnubberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerSnubber
     */
    private $snubber;
    private $application;
    private $fileWriter;

    public function setUp()
    {
        $this->application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileWriter = $this->getMockBuilder(FileWriter::class)
            ->getMock();

        $this->snubber = new ControllerSnubber($this->application, $this->fileWriter);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(ControllerSnubber::class, $this->snubber);
    }

    public function testGenerateControllerSnub()
    {
        $plugins = ['zfcUserAuthentication' => 'zfcuserauthentication'];


        $controllerPluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()->getMock();

        $controllerPluginManager->expects($this->once())
            ->method('getCanonicalNames')
            ->willReturn($plugins);

        $controllerPluginManager->expects($this->any())
            ->method('get');

        $serviceManager = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->expects($this->once())
            ->method('get')
            ->with('controller-plugin-manager')
            ->will($this->returnValue($controllerPluginManager));

        $this->application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $this->fileWriter
            ->expects($this->once())
            ->method('write');

        $this->snubber->generateControllerStub(AbstractActionController::class);
    }
}
