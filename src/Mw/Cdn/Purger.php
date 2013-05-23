<?php

namespace Mw\Cdn;

use Monolog\Logger;
use Mw\Cdn\Exception\MaximumFileException;

/**
 * Class Purger
 * @package Mw\Cdn
 *
 * @usage
 * $purger = new Mw\Cdn\Purger();
 * $purger
 *     ->setNotificationEmail('sys.admin@my-wardrobe.com')
 *     ->addUrl('htp://cdn11.my-wardrobe.com/images/products/9/2/928786/t_928786.jpg')
 *     ->purge();
 */
class Purger
{
    /**
     * @var string
     */
    protected $server = 'https://ccuapi.akamai.com/ccuapi-axis.wsdl';

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
    protected $action = 'invalidate';

    /**
     * @var string
     */
    protected $domain = 'production';

    /**
     * @var string
     */
    protected $type = 'arl';

    /**
     * @var string
     */
    protected $notificationEmail;

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * @var \SoapClient
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
     * @param string $user     Akamai Username
     * @param string $password Akamai password
     * @param string $server   Akamai wsdl server url
     * @param Logger $logger   Monolog logger
     */
    public function __construct($user, $password, $server = null, Logger $logger = null)
    {
        $this->user = $user;
        $this->password = $password;

        if (is_null($logger)) {
            $this->logger = new Logger('akamai_purger');
        }

        if (is_null($server)) {
            $this->server = $server;
        }
    }

    /**
     * Set Logger
     *
     * @param Logger $logger
     *
     * @return Purger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
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
     * Set Notification Email
     *
     * @param string $email
     *
     * @return Purger
     */
    public function setNotificationEmail($email)
    {
        $this->notificationEmail = $email;

        return $this;
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
        if (count($this->urls) == 1000) {
            $this->logger->error(json_encode(array('error' => 'The maximium number of items that can be purge at one time is 1000')));
            throw new MaximumFileException('The maximium number of items that can be purge at one time is 1000');
        }
        $this->urls[] = $url;

        return $this;
    }

    /**
     * Purge
     *
     * @return bool
     */
    public function purge()
    {
        $success = false;

        $this->client = new \SoapClient($this->server, array(
            'trace' => 1,
            'exceptions' => 1,
            'features' => SOAP_USE_XSI_ARRAY_TYPE
        ));

        $options = array(
            'action=' . $this->action,
            'domain=' . $this->domain,
            'type=' . $this->type,
        );

        if (null !== $this->notificationEmail) {
            $options[] = 'email-notification=' . $this->notificationEmail;
        }

        try {
            $this->response = $this->client->purgeRequest($this->user, $this->password, '', $options, $this->urls);

            $this->logger->info(json_encode(array('sessionID' => $this->response->sessionID, 'options' => $options, 'urls' => $this->urls)));

            switch ($this->response->resultCode) {
                case 100:
                    $success = true;
                    $this->logger->info(json_encode($this->response));
                    break;
                default:
                    $this->logger->error(json_encode($this->response));
            }
        } catch (\SoapFault $e) {
            $this->logger->info(json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode())));
        }

        return $success;
    }
}