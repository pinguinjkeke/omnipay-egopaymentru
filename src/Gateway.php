<?php

namespace Omnipay\EgopaymentRu;

use Guzzle\Http\ClientInterface;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\EgopaymentRu\Message\SoapAbstractRequest;
use SoapClient;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Egopay.ru payment gateway provider
 * Supports order registration
 * 
 * Link attached was very helpful for building gateway based on SOAP
 * 
 * @link https://github.com/delatbabel/omnipay-alliedwallet/blob/master/src/Gateway.php
 */
class Gateway extends AbstractGateway
{
    /**
     * Test order endpoint address
     *
     * @var string
     */
    protected $testOrderEndpoint = 'https://tws.egopay.ru/order/v2/';

    /**
     * Live order endpoint address
     *
     * @var string
     */
    protected $liveOrderEndpoint = '';

    /**
     * Test status endpoint address
     *
     * @var string
     */
    protected $testStatusEndpoint = 'https://tws.egopay.ru/status/v4/';

    /**
     * Live status endpoint address
     *
     * @var string
     */
    protected $liveStatusEndpoint = '';

    /**
     * SoapClient
     *
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * Create a new gateway instance, soapClient added to constructor
     *
     * @param ClientInterface $httpClient A Guzzle client to make API calls with
     * @param HttpRequest $httpRequest A Symfony HTTP request object
     * @param SoapClient $soapClient A SPL SoapClient
     */
    public function __construct(
        ClientInterface $httpClient = null,
        HttpRequest $httpRequest = null,
        SoapClient $soapClient = null
    ) {
        parent::__construct($httpClient, $httpRequest);
        $this->soapClient = $soapClient;
    }

    /**
     * Create and initialize a request object
     *
     * This function is usually used to create objects of type
     * Omnipay\Common\Message\AbstractRequest (or a non-abstract subclass of it)
     * and initialise them with using existing parameters from this gateway.
     *
     * @see \Omnipay\Common\Message\AbstractRequest
     * @param string $class The request class name
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    protected function createRequest($class, array $parameters)
    {
        /** @var SoapAbstractRequest $obj */
        $obj = new $class($this->httpClient, $this->httpRequest, $this->soapClient);

        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

    /**
     * Get gateway display name
     * This can be used by carts to get the display name for each gateway.
     *
     * @return string
     */
    public function getName()
    {
        return 'Egopayment';
    }

    /**
     * Define gateway parameters, in the following format:
     *
     * array(
     *     'username' => '', // string variable
     *     'testMode' => false, // boolean variable
     *     'landingPage' => array('billing', 'login'), // enum variable, first item is default
     * );
     */
    public function getDefaultParameters()
    {
        return array(
            'wsdl' => __DIR__ . '/Resource/orderv2_new.xml',
            'endpoint' => $this->getTestMode() ? $this->testOrderEndpoint : $this->liveOrderEndpoint,
            'url_ok' => '/ok/',
            'url_fault' => '/fault/',
            'shop_id' => '',
            'number' => '',
            'user' => '',
            'pass' => '',
            'timelimit' => '',
            'paytype' => 'card',
            'currency' => 'RUB',
            'language' => 'ru'
        );
    }

    /**
     * Get WSDL file path
     *
     * @return mixed
     */
    public function getWsdl()
    {
        return $this->getParameter('wsdl');
    }

    /**
     * Set WSDL file path
     *
     * @param string $wsdl
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setWsdl($wsdl)
    {
        if (!file_exists($wsdl)) {
            throw new RuntimeException("WSDL file not exists at \"{$wsdl}\"");
        }

        return $this->setParameter('wsdl', $wsdl);
    }

    /**
     * Returns endpoint address
     * 
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getParameter('endpoint');
    }

    /**
     * Set status or order endpoint with or without test mode
     *
     * @param bool $statusEndpoint Status or order endpoint
     * @return string
     */
    public function setEndpoint($statusEndpoint = false)
    {
        if ($statusEndpoint) {
            $endpoint = $this->getTestMode() ? $this->testStatusEndpoint : $this->liveStatusEndpoint;
        } else {
            $endpoint = $this->getTestMode() ? $this->testOrderEndpoint : $this->liveOrderEndpoint;
        }

        return $this->setParameter('endpoint', $endpoint);
    }

    /**
     * Get shop id you received from Egopayment
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->getParameter('shop_id');
    }

    /**
     * Set shop id you received from Egopayment
     *
     * @param int $shopId
     * @return $this
     */
    public function setShopId($shopId)
    {
        return $this->setParameter('shop_id', $shopId);
    }

    /**
     * Get current order number inside your application
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getParameter('number');
    }

    /**
     * Get current order number inside your application
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setParameter('number', $orderId);
    }

    /**
     * Get username you received from Egopayment
     *
     * @return string
     */
    public function getUser()
    {
        return $this->getParameter('user');
    }

    /**
     * Set username you received from Egopayment
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        return $this->setParameter('user', $user);
    }

    /**
     * Get password you received from Egopayment
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Set password you received from Egopayment
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        return $this->setParameter('password', $password);
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCurrency($currency)
    {
        $currencies = ['RUB', 'EUR', 'USD'];

        if (!in_array($currency, $currencies, true)) {
            throw new RuntimeException(
                'Currency must be one of [' . implode(',', $currencies) . "], but {$currency} given."
            );
        }

        return $this->setParameter('currency', $currency);
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * Set language
     *
     * @param string $language Language from ['ru', 'en', 'de', 'cn']
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     * @link https://tws.egopay.ru/docs/v2/#p-7.5
     */
    public function setLanguage($language)
    {
        $languages = array('ru', 'en', 'de', 'cn');

        if (!in_array($language, $languages, true)) {
            throw new RuntimeException(
                'Language must be one of ' . implode(', ', $languages) . ", but {$language} given"
            );
        }

        return $this->setParameter('language', $language);
    }

    /**
     * Cancel request
     *
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     * @link https://tws.egopay.ru/docs/v2/#p-5
     */
    public function cancel(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Ego\Message\CancelRequest', $parameters);
    }

    /**
     * Your application send order registration request to gateway.
     *
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     * @link https://tws.egopay.ru/docs/v2/#p-3.1
     */
    public function register(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Ego\Message\RegisterRequest', $parameters);
    }
}
