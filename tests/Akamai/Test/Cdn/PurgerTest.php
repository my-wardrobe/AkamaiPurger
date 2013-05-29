<?php

namespace Akamai\Test\Cdn;

use Akamai\Cdn\Purger;
use Psr\Log\NullLogger;
use Mockery;

/**
 * Class PurgerTest
 *
 * @package Akamai\Test\Cdn
 */
class PurgerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Set and Get WSDL
     */
    public function testSetAndGetWsdl()
    {
        $defaultWsdl = 'https://ccuapi.akamai.com/ccuapi-axis.wsdl';
        $wsdl = 'http://example.com/acme.wsdl';

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultWsdl, $purger->getWsdl());
        $this->assertSame($purger, $purger->setWsdl($wsdl), 'Expected Fluid Interface');
        $this->assertSame($wsdl, $purger->getWsdl());
    }

    public function testSetWsdlFromConstructor()
    {
        $wsdl = 'http://example.com/acme.wsdl';

        $purger = new Purger('username', 'password', $wsdl);

        $this->assertSame($wsdl, $purger->getWsdl());
    }

    /**
     * Test Set and Get Action
     */
    public function testSetAndGetAction()
    {
        $defaultAction = 'invalidate';
        $action = 'remove';

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultAction, $purger->getAction());
        $this->assertSame($purger, $purger->setAction($action), 'Expected Fluid Interface');
        $this->assertSame($action, $purger->getAction());
    }

    /**
     * Test Set and Get Domain
     */
    public function testSetAndGetDomain()
    {
        $defaultDomain = 'staging';
        $domain = 'production';

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultDomain, $purger->getDomain());
        $this->assertSame($purger, $purger->setDomain($domain), 'Expected Fluid Interface');
        $this->assertSame($domain, $purger->getDomain());
    }

    /**
     * Test Set and Get Type
     */
    public function testSetAndGetType()
    {
        $defaultType = 'arl';
        $type = 'url';

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultType, $purger->getType());
        $this->assertSame($purger, $purger->setType($type), 'Expected Fluid Interface');
        $this->assertSame($type, $purger->getType());
    }

    /**
     * Test Add and Get Notification Emails
     */
    public function testAddAndGetNotificationEmails()
    {
        $defaultNotificationEmails = array();
        $notificationEmails = array(
            'dan@my-wardrobe.com',
            'mauro@my-wardrobe.com',
        );

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultNotificationEmails, $purger->getNotificationEmails());
        foreach ($notificationEmails as $notificationEmail) {
            $this->assertSame($purger, $purger->addNotificationEmail($notificationEmail), 'Expected Fluid Interface');
        }
        $this->assertSame($notificationEmails, $purger->getNotificationEmails());
    }

    /**
     * Test Add and Get URLs
     */
    public function testAddAndGetURLs()
    {
        $defaultURLs = array();
        $urls = array(
            'http://www.exam§ple.com/acme/asset-1.png',
            'http://www.example.com/acme/asset-2.png',
        );

        $purger = new Purger('username', 'password');

        $this->assertSame($defaultURLs, $purger->getUrls());
        foreach ($urls as $url) {
            $this->assertSame($purger, $purger->addUrl($url), 'Expected Fluid Interface');
        }
        $this->assertSame($urls, $purger->getUrls());
    }

    /**
     * Test Add URL limit throws exception
     *
     * @expectedException \Akamai\Cdn\Exception\MaximumFileException
     */
    public function testAddUrlLimitThrowsException()
    {
        $purger = new Purger('username', 'password');

        for ($x=0; $x<Purger::MAX_PURGE_URLS; $x++) {
            $purger->addUrl('http://www.example.com/acme/asset-' . $x . '.png');
        }
    }

    /**
     * Test Set Logger
     */
    public function testSetLogger()
    {
        $logger = new NullLogger();

        $purger = new Purger('username', 'password');
        $this->assertSame($purger, $purger->setLogger($logger), 'Expected Fluid Interface');
    }

    /**
     * Test Set and Get Client
     */
    public function testSetAndGetClient()
    {
        $client = Mockery::Mock('\SoapClient');

        $purger = new Purger('username', 'password');
        $this->assertSame($purger, $purger->setClient($client), 'Expected Fluid Interface');
        $this->assertSame($client, $purger->getClient());
    }

    /**
     * Test Purge failure throws exception
     *
     * @expectedException \Akamai\Cdn\Exception\PurgeFailureException
     */
    public function testPurgeFailureThrowsException()
    {
        $client = Mockery::Mock('\SoapClient');
        $client->shouldReceive('purgeRequest')->once()->andThrow('\Akamai\Cdn\Exception\SoapFaultException', 'null', 500);

        $purger = new Purger('username', 'password');
        $purger->setClient($client);

        $purger->purge();
    }

    /**
     * Test Purge Failure returns False
     */
    public function testPurgeFailureReturnsFalse()
    {
        $soapResponse = new \stdClass();
        $soapResponse->sessionID = '123456';
        $soapResponse->resultCode = '500';

        $client = Mockery::Mock('\SoapClient');
        $client->shouldReceive('purgeRequest')->once()->andReturn($soapResponse);

        $purger = new Purger('username', 'password');
        $purger->setClient($client);

        $this->assertFalse($purger->purge());
    }

    /**
     * Test Purge Success returns True
     */
    public function testPurgeSuccessReturnsTrue()
    {
        $soapResponse = new \stdClass();
        $soapResponse->sessionID = '123456';
        $soapResponse->resultCode = '100';

        $client = Mockery::Mock('\SoapClient');
        $client->shouldReceive('purgeRequest')->once()->andReturn($soapResponse);

        $purger = new Purger('username', 'password');
        $purger->setClient($client);

        $this->assertTrue($purger->purge());
    }
}
