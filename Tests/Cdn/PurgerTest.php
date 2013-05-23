<?php
/**
 * Short description file...
 *
 * Long description file (if need)...
 *
 * @package Mw\\Tests
 * @author  maurogadaleta
 * @date    23/04/2013 16:22
 */
namespace Mw\Tests\Cdn;

use Monolog\Logger;
use Mw\Cdn\Purger;

/**
 * QuantcastService Wen test case
 */
class PurgerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Purger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purger;

    private $logger;

    public function testSetters()
    {
        $this->assertSame($this->purger, $this->purger->setAction('foo'));
        $this->assertSame($this->purger, $this->purger->setDomain('foo'));
        $this->assertSame($this->purger, $this->purger->setType('foo'));
        $this->assertSame($this->purger, $this->purger->setNotificationEmail('foo'));
    }

    /**
     * @expectedException \Mw\Cdn\Exception\MaximumFileException
     */
    public function testAddUrlThrowException()
    {
        for ($i = 0; $i <= 1000; $i++) {
            $this->assertSame($this->purger, $this->purger->addUrl('foo'));
        }
    }

    public function testPurge()
    {
        $this->assertFalse($this->purger->purge());
    }

    protected function setUp()
    {
        $this->purger = $this->getMockBuilder('Mw\Tests\Cdn\PurgerFake')
            ->setMethods(array('foo'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder('\Monolog\Logger')
            ->setConstructorArgs(array('foo'))
            ->enableOriginalConstructor()
            ->getMock();

        $this->purger->setLogger($this->logger);
    }
}