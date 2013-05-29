<?php

namespace Akamai\Cdn;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SoapClient;
use Akamai\Cdn\Exception\MaximumFileException;
use Akamai\Cdn\Exception\SoapFaultException;
use Akamai\Cdn\Exception\PurgeFailureException;

/**
 * Class Purger
 * @package Akamai\Cdn
 *
 * @usage
 * $purger = new Akamai\Cdn\Purger();
 * $purger
 *     ->setNotificationEmail('acme@exmaple.com')
 *     ->addUrl('htp://www.example.com/acme/asset.png')
 *     ->purge();
 */
class Purger
{
    const MAX_PURGE_URLS = 1000;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $wsdl = 'https://ccuapi.akamai.com/ccuapi-axis.wsdl';

    /**
     * @var string
     */
    protected $action = 'invalidate';

    /**
     * @var string
     */
    protected $domain = 'staging';

    /**
     * @var string
     */
    protected $type = 'arl';

    /**
     * @var array
     */
    protected $notificationEmails = array();

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * @var SoapClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param string          $user     Akamai Username
     * @param string          $password Akamai password
     * @param string          $wsdl     Akamai wsdl url
     * @param LoggerInterface $logger   Psr Logger
     */
    public function __construct($user, $password, $wsdl = null, LoggerInterface $logger = null)
    {
        $this->user = $user;
        $this->password = $password;

        if (false === is_null($wsdl)) {
            $this->wsdl = $wsdl;
        }

        $this->logger = $logger;

        if (true === is_null($logger)) {
            $this->logger = new NullLogger();
        }
    }

    /**
     * Set WSDL
     *
     * @param string $wsdl
     *
     * @return Purger
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;

        return $this;
    }

    /**
     * Get WSDL
     *
     * @return null|string
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }

    /**
     * Set Action
     *
     * @param string $action
     *
     * @return Purger
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get Action
     *
     * @return null|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set Domain
     *
     * @param string $domain
     *
     * @return Purger
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get Domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set Type
     *
     * @param string $type
     *
     * @return Purger
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add Notification Email
     *
     * @param string $email
     *
     * @return Purger
     */
    public function addNotificationEmail($email)
    {
        $this->notificationEmails[] = $email;

        return $this;
    }

    /**
     * Get Notification Emails
     *
     * @return array
     */
    public function getNotificationEmails()
    {
        return $this->notificationEmails;
    }

    /**
     * Add URL
     *
     * @param string $url
     *
     * @return Purger
     * @throws MaximumFileException
     */
    public function addUrl($url)
    {
        $this->urls[] = $url;

        if (self::MAX_PURGE_URLS === count($this->urls)) {
            $this->logger->error(json_encode(array('error' => 'The maximum number of items that can be purge at one time is 1000')));

            throw new MaximumFileException('The maximum number of items that can be purge at one time is 1000');
        }

        return $this;
    }

    /**
     * Get URLs
     *
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Set Logger
     *
     * @param LoggerInterface $logger Psr Logger
     *
     * @return Purger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set Client
     *
     * @param SoapClient $client Soap Client
     *
     * @return Purger
     */
    public function setClient(SoapClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get Client
     *
     * @return SoapClient
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new SoapClient($this->wsdl, array(
                'trace' => 1,
                'exceptions' => 1,
                'features' => SOAP_USE_XSI_ARRAY_TYPE
            ));
        }

        return $this->client;
    }

    /**
     * Purge
     *
     * @return bool
     */
    public function purge()
    {
        $options = $this->compileOptions();

        try {
            $this->response = $this->client->purgeRequest($this->user, $this->password, '', $options, $this->urls);
            $this->logger->info(json_encode(array('sessionID' => $this->response->sessionID, 'options' => $options, 'urls' => $this->urls)));

            switch ($this->response->resultCode) {
                case 100:
                    $this->logger->info(json_encode($this->response));
                    return true;
                    break;
                default:
                    $this->logger->error(json_encode($this->response));
            }
        } catch (SoapFaultException $e) {
            $this->logger->info(json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode())));
            throw new PurgeFailureException($e->getMessage(), $e->getCode());
        }

        return false;
    }

    /**
     * Compile Soap Options
     *
     * @return array
     */
    protected function compileOptions()
    {
        $options = array(
            'action=' . $this->action,
            'domain=' . $this->domain,
            'type=' . $this->type,
        );

        if (false === empty($this->notificationEmails)) {
            $options[] = 'email-notification=' . implode(',', $this->notificationEmails);
        }

        return $options;
    }
}