<?php

namespace Omnipay\EgopayRu\Message;

use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Tests\TestCase;

/**
 * You must provide class name of the request to test it
 * and valid parameters which run the request
 */
abstract class AbstractRequestTest extends TestCase
{
    /**
     * Request object
     *
     * @var SoapAbstractRequest
     */
    protected $request;

    /**
     * Generated shop id
     *
     * @var int
     */
    protected $shopId;

    /**
     * Generated order id
     *
     * @var int
     */
    protected $orderId;

    /**
     * Request class name
     *
     * @return string
     */
    abstract protected function getRequestClassName();

    /**
     * Request parameters
     *
     * @return array
     */
    abstract protected function getRequestParameters();

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setUp()
    {
        $requestClass = '\\Omnipay\\EgopayRu\\Message\\' . $this->getRequestClassName();

        list($this->shopId, $this->orderId) = array(mt_rand(10000, 20000), mt_rand(1, 100));
        
        if (!class_exists($requestClass)) {
            throw new RuntimeException("Cannot find \"{$requestClass}\" class");
        }

        $this->request = new $requestClass($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize($this->getRequestParameters());
    }

    /**
     * Test all parameters
     */
    public function testParameters()
    {
        foreach ($this->getRequestParameters() as $parameter => $value) {
            $getter = 'get' . ucfirst($this->camelCase($parameter));
            $this->assertSame($value, $this->request->{$getter}());
        }
    }

    /**
     * Test data array (getData)
     */
    abstract public function testData();
}
