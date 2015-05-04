<?php
/**
 * Created by Gary Hockin.
 * Date: 03/05/15
 * @GeeH
 */

namespace SnubbedTest;


use Snubbed\FileWriter;
use Snubbed\ViewSnubber;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;

class ViewSnubberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewSnubber
     */
    private $snubber;

    private function setUp()
    {

        $serviceManager = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->expects($this->once())
            ->method('get')
            ->with('controller-manager');

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $application->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $fileWriter = $this->getMockBuilder(FileWriter::class)
            ->getMock();

        $this->snubber = new ViewSnubber($application, $fileWriter);

    }

    public function testConstructor()
    {
        $this->assertInstanceOf(ViewSnubber::class, $this->snubber);
    }

    public function testGenerateSnubs()
    {

    }

}
