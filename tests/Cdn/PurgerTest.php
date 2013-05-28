<?php

namespace Akamai\Tests\Cdn;

use MockFs\MockFs;
use Monolog\Logger;
use Akamai\Cdn\Purger;

/**
 * Class PurgerTest
 *
 * @package Akamai\Tests\Cdn
 */
class PurgerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Purger
     */
    private $purger;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Test Setters
     */
    public function testSetters()
    {
        $this->assertSame($this->purger, $this->purger->setAction('foo'));
        $this->assertSame($this->purger, $this->purger->setDomain('foo'));
        $this->assertSame($this->purger, $this->purger->setType('foo'));
        $this->assertSame($this->purger, $this->purger->setNotificationEmail('foo'));
    }

    /**
     * Test Add Url Throws Exception
     *
     * @expectedException \Akamai\Cdn\Exception\MaximumFileException
     */
    public function testAddUrlThrowException()
    {
        for ($i = 0; $i <= 1000; $i++) {
            $this->assertSame($this->purger, $this->purger->addUrl('foo'));
        }
    }

    /**
     * Test Purge
     */
    public function testPurge()
    {
        $this->assertFalse($this->purger->purge());
    }

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->purger = $this->getMockBuilder('Akamai\Tests\Cdn\PurgerFake')
            ->setMethods(array('foo'))
            ->disableOriginalConstructor()
            ->getMock();

        $mockFs = new MockFs();
        $mockFs->getFileSystem()->addFile(
            'ccuapi-axis.wsdl',
            file_get_contents(__DIR__ . '/../ccuapi-axis.wsdl'),
            '/'
        );
        $this->purger->setServer('mfs://ccuapi-axis.wsdl');

        $this->logger = $this->getMockBuilder('\Monolog\Logger')
            ->setConstructorArgs(array('foo'))
            ->enableOriginalConstructor()
            ->getMock();

        $this->purger->setLogger($this->logger);
    }
}