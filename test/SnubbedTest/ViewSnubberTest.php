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

class ViewSnubberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewSnubber
     */
    private $snubber;

    public function setUp()
    {
        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileWriter = $this->getMockBuilder(FileWriter::class)
            ->getMock();

        $this->snubber = new ViewSnubber($application, $fileWriter);

        $this->assertInstanceOf(ViewSnubber::class, $this->snubber);
    }

    public function testGenerateViewSnubs()
    {
        $this->assertTrue(true);
    }
}
