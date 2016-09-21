<?php

namespace Omnipay\EgopayRu;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\EgopayRu\Message\CancelResponse;
use Omnipay\EgopayRu\Message\GetByOrderResponse;
use Omnipay\EgopayRu\Message\RefundResponse;
use Omnipay\EgopayRu\Message\RegisterResponse;
use Omnipay\EgopayRu\Message\RejectResponse;
use Omnipay\Tests\GatewayTestCase;

require __DIR__ . '/GatewayMockValues.php';

/**
 * Base Gateway Test class
 *
 * Ensures all gateways conform to consistent standards
 */
class GatewayTest extends GatewayTestCase
{
    /**
     * Gateway
     *
     * @var Gateway
     */
    protected $gateway;

    /**
     * Soap Client
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\SoapClient
     */
    protected $mockSoapClient;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $wsdl = realpath(__DIR__ . '/../src/Resource/orderv2_new.xml');
        $this->mockSoapClient = $this->getMockFromWsdl($wsdl, 'EgopayRuSoapOrderClient');

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest(), $this->mockSoapClient);
        $this->gateway->setTestMode(true);
        $this->gateway->setShopId('123456')
            ->setOrderId(123)
            ->setUser('hello')
            ->setPassword('world')
            ->setWsdl($wsdl);
    }

    /**
     * Some gateway parameters works with specific values
     * Currency accepts only EUR, RUB, USD,
     * Wsdl accepts real file and etc
     *
     * @param string $key
     * @return mixed
     */
    private function getParameterValue($key)
    {
        switch ($key) {
            case 'wsdl':
                return realpath(__DIR__ . '/../src/Resource/orderv2_new.xml');
            case 'currency':
                return 'EUR';
            case 'language':
                return 'en';
            case 'endpoint':
                return 'https://tws.egopay.ru/order/v2/';
            case 'testMode':
                return true;
        }

        return uniqid('', true);
    }

    /**
     * Check if parameters applied to gateway is applied on request object
     *
     * @param string $key
     * @param AbstractRequest $request
     */
    private function assertParameters($key, AbstractRequest $request)
    {
        $getter = 'get' . ucfirst($this->camelCase($key));
        $setter = 'set' . ucfirst($this->camelCase($key));
        $value = $this->getParameterValue($key);
        $this->gateway->{$setter}($value);

        $this->assertSame($value, $request->{$getter}());
    }

    /**
     * All default parameters must have getter and setter
     */
    public function testDefaultParametersHaveMatchingMethods()
    {
        $settings = $this->gateway->getDefaultParameters();

        foreach ($settings as $key => $default) {
            $getter = 'get' . ucfirst($this->camelCase($key));
            $setter = 'set' . ucfirst($this->camelCase($key));
            $value = $this->getParameterValue($key);

            $this->assertTrue(method_exists($this->gateway, $getter), "Gateway must implement {$getter}()");
            $this->assertTrue(method_exists($this->gateway, $setter), "Gateway must implement {$setter}()");

            // setter must return instance
            $this->assertSame($this->gateway, $this->gateway->$setter($value));
            $this->assertSame($value, $this->gateway->$getter());
        }
    }

    public function testCancelParameters()
    {
        if (!$this->gateway->supportsCancel()) {
            return;
        }

        $request = $this->gateway->cancel();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            $this->assertParameters($key, $request);
        }
    }

    public function testCancelSuccess()
    {
        if (!$this->gateway->supportsCancel()) {
            return;
        }

        list($orderId, $shopId) = array(mt_rand(1, 999), mt_rand(10000, 20000));
        
        $this->mockSoapClient->expects($this->any())
            ->method('cancel')
            ->will($this->returnValue(GatewayMockValues::getCancelSuccess($shopId, $orderId)));

        /** @var CancelResponse $response */
        $response = $this->gateway->cancel(array(
            'shop_id' => $shopId,
            'order_id' => $orderId
        ))->send();
        
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($data['order']['shop_id'], $shopId);
        $this->assertEquals($data['order']['number'], $orderId);
    }

    public function testCancelFail()
    {
        if (!$this->gateway->supportsCancel()) {
            return;
        }

        $this->mockSoapClient->expects($this->any())
            ->method('cancel')
            ->will($this->returnValue(GatewayMockValues::getCancelFail()));

        /** @var CancelResponse $response */
        $response = $this->gateway->cancel(array(
            'order' => array('shop_id' => mt_rand(1, 999), 'number' => mt_rand(10000, 20000))
        ))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($response->getData(), GatewayMockValues::getCancelFail());
    }

    public function testConfirmParameters()
    {
        if (!$this->gateway->supportsConfirm()) {
            return;
        }

        $request = $this->gateway->confirm();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            $this->assertParameters($key, $request);
        }
    }
    
    public function testConfirm()
    {
        if (!$this->gateway->supportsConfirm()) {
            return;
        }

        // TODO Test confirm
    }

    public function testRejectParameters()
    {
        if (!$this->gateway->supportsReject()) {
            return;
        }

        $request = $this->gateway->reject();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            $this->assertParameters($key, $request);
        }
    }

    public function testRejectSuccess()
    {
        if (!$this->gateway->supportsReject()) {
            return;
        }

        list($orderId, $shopId, $paymentId) = array(mt_rand(1, 999), mt_rand(10000, 20000), mt_rand(100000, 500000));

        $this->mockSoapClient->expects($this->any())
            ->method('reject')
            ->will($this->returnValue(GatewayMockValues::getRejectSuccess($shopId, $orderId)));

        /** @var RejectResponse $response */
        $response = $this->gateway->reject(array(
            'shop_id' => $shopId,
            'order_id' => $orderId,
            'payment_id' => $paymentId
        ))->send();
        
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($data['order']['shop_id'], $shopId);
        $this->assertEquals($data['order']['number'], $orderId);
        $this->assertTrue(in_array($data['status'], array('in_progress', 'canceled'), true));
    }

    public function testRejectFail()
    {
        if (!$this->gateway->supportsReject()) {
            return;
        }

        list($orderId, $shopId) = array(mt_rand(1, 999), mt_rand(10000, 20000));

        $this->mockSoapClient->expects($this->any())
            ->method('reject')
            ->will($this->returnValue(GatewayMockValues::getRejectFail()));

        /** @var RejectResponse $response */
        $response = $this->gateway->reject(array(
            'order' => array('shop_id' => $shopId, 'number' => $orderId),
            'payment_id' => '123456'
        ))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($response->getData(), GatewayMockValues::getRejectFail());
    }

    public function testRefundParameters()
    {
        if (!$this->gateway->supportsRefund()) {
            return;
        }

        $request = $this->gateway->refund();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            $this->assertParameters($key, $request);
        }
    }

    public function testRefundSuccess()
    {
        if (!$this->gateway->supportsRefund()) {
            return;
        }

        list($orderId, $shopId, $paymentId) = array(mt_rand(1, 999), mt_rand(10000, 20000), mt_rand(100000, 500000));

        $this->mockSoapClient->expects($this->any())
            ->method('refund')
            ->will($this->returnValue(GatewayMockValues::getRefundSuccess($shopId, $orderId)));

        /** @var RefundResponse $response */
        $response = $this->gateway->refund(array(
            'shop_id' => $shopId,
            'order_id' => $orderId,
            'refund_id' => "refund1_{$paymentId}",
            'payment_id' => $paymentId,
            'amount' => 10.0,
            'currency' => 'RUB'
        ))->send();
        
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($data['order']['shop_id'], $shopId);
        $this->assertEquals($data['order']['number'], $orderId);
        $this->assertTrue(in_array(
            $data['status'],
            array('acknowledged', 'in_progress', 'canceled'),
            true
        ));
    }

    public function testRefundFail()
    {
        if (!$this->gateway->supportsRefund()) {
            return;
        }

        list($orderId, $shopId, $paymentId) = array(mt_rand(1, 999), mt_rand(10000, 20000), mt_rand(100000, 500000));

        $this->mockSoapClient->expects($this->any())
            ->method('refund')
            ->will($this->returnValue(GatewayMockValues::getRefundFail()));

        /** @var RefundResponse $response */
        $response = $this->gateway->refund(array(
            'shop_id' => $shopId,
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'refund_id' => "refund1_{$paymentId}",
            'amount' => 10.0,
            'currency' => 'RUB'
        ))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($response->getData(), GatewayMockValues::getRefundFail());
    }

    public function testRegisterParameters()
    {
        if (!$this->gateway->supportsRegister()) {
            return;
        }

        $request = $this->gateway->register();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            $this->assertParameters($key, $request);
        }
    }

    public function testRegisterSuccess()
    {
        if (!$this->gateway->supportsRegister()) {
            return;
        }

        list($shopId, $orderId, $session) = array(mt_rand(10000, 20000), mt_rand(1, 100), uniqid('', true));
        $redirectUrl = 'https://sandbox.egopay.ru/payments/request';

        $this->mockSoapClient->expects($this->any())
            ->method('register_online')
            ->will($this->returnValue(GatewayMockValues::getRegisterSuccess($redirectUrl, $session)));

        /** @var RegisterResponse $response */
        $response = $this->gateway->register(array(
            'shop_id' => $shopId,
            'amount' => 10.00,
            'currency' => 'RUB',
            'order_id' => $orderId,
            'url_ok' => '/payment/ok/',
            'url_fault' => '/payment/fail/',
            'language' => 'ru',
            'paytype' => 'card'
        ))->setCustomer(array(
            'id' => 10,
            'name' => 'John Doe',
            'phone' => '+7 (999) 626-45-13',
            'email' => 'a@b.ru'
        ))->addItem(array(
            'number' => 2,
            'typename' => 'service',
            'amount' => array('amount' => 10.00, 'currency' => 'RUB'),
            'host' => ''
        ))->send();
        
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals($response->getRedirectUrl(), "{$redirectUrl}?session={$session}");
        $this->assertEquals($response->getTransactionReference(), $session);
        $this->assertEquals($response->getRedirectMethod(), 'GET');
    }

    public function testRegisterFail()
    {
        if (!$this->gateway->supportsRegister()) {
            return;
        }

        if (!$this->gateway->supportsRegister()) {
            return;
        }

        $this->mockSoapClient->expects($this->any())
            ->method('register_online')
            ->will($this->returnValue(GatewayMockValues::getRegisterFail()));

        /** @var RegisterResponse $response */
        $response = $this->gateway->register(array(
            'shop_id' => mt_rand(10000, 20000),
            'amount' => 10.00,
            'currency' => 'RUB',
            'order_id' => mt_rand(1, 100),
            'url_ok' => '/payment/ok/',
            'url_fault' => '/payment/fail/',
            'language' => 'ru',
            'paytype' => 'card'
        ))->setCustomer(array(
            'id' => 10,
            'name' => 'John Doe',
            'phone' => '+7 (999) 626-45-13',
            'email' => 'a@b.ru'
        ))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($response->getData(), GatewayMockValues::getRegisterFail());
    }

    public function testStatusParameters()
    {
        if (!$this->gateway->supportsStatus()) {
            return;
        }

        $request = $this->gateway->status();

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            // Status has another wsdl and endpoint
            if ($key === 'endpoint') {
                $endpoint = 'https://tws.egopay.ru/status/v4/';

                $this->gateway->setEndpoint($endpoint);
                $this->assertSame($endpoint, $request->getEndpoint());
            } elseif ($key === 'wsdl') {
                $wsdl = realpath(__DIR__ . '/../src/Resource/statusv4.xml');

                $this->gateway->setWsdl($wsdl);
                $this->assertSame($wsdl, $request->getWsdl());
            } else {
                $this->assertParameters($key, $request);
            }
        }
    }

    public function testStatusSuccess()
    {
        if (!$this->gateway->supportsStatus()) {
            return;
        }

        list($shopId, $orderId) = array(mt_rand(10000, 20000), mt_rand(1, 100));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\SoapClient $mockSoapClient */
        $mockSoapClient = $this->getMockFromWsdl(__DIR__ . '/../src/Resource/statusv4.xml', 'EgopayRuSoapStatusClient');
        $this->gateway->setSoapClient($mockSoapClient);

        $mockSoapClient->expects($this->any())
            ->method('get_by_order')
            ->will($this->returnValue(GatewayMockValues::getStatusSuccess($shopId, $orderId)));

        /** @var GetByOrderResponse $response */
        $response = $this->gateway->status(array(
            'shop_id' => $shopId,
            'order_id' => $orderId
        ))->send();
        
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($data['order']['shop_id'], $shopId);
        $this->assertEquals($data['order']['order_id'], $orderId);

        $this->gateway->setSoapClient($this->mockSoapClient);
    }

    public function testStatusFail()
    {
        if (!$this->gateway->supportsStatus()) {
            return;
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|\SoapClient $mockSoapClient */
        $mockSoapClient = $this->getMockFromWsdl(__DIR__ . '/../src/Resource/statusv4.xml', 'EgopayRuSoapStatusClient');
        $this->gateway->setSoapClient($mockSoapClient);

        $mockSoapClient->expects($this->any())
            ->method('get_by_order')
            ->will($this->returnValue(GatewayMockValues::getStatusFail()));

        $response = $this->gateway->status(array(
            'shop_id' => mt_rand(10000, 20000),
            'order_id' => mt_rand(1, 100)
        ))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals($response->getData(), GatewayMockValues::getStatusFail());

        $this->gateway->setSoapClient($this->mockSoapClient);
    }
}

