<?php

namespace Omnipay\EgopayRu\Message;

class RegisterRequestTest extends AbstractRequestTest
{
    /**
     * Customer parameters
     *
     * @var array
     */
    protected $customer;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setUp()
    {
        $this->customer = array(
            'id' => mt_rand(1, 100),
            'name' => 'Vasya Pupkin',
            'email' => 'a@b.ru',
            'phone' => '1234567890'
        );

        parent::setUp();
    }
    /**
     * Request class name
     *
     * @return string
     */
    protected function getRequestClassName()
    {
        return 'RegisterRequest';
    }

    /**
     * Request parameters
     *
     * @return array
     */
    protected function getRequestParameters()
    {
        return array(
            'shop_id' => $this->shopId,
            'order_id' => $this->orderId,
            'user' => $this->user,
            'password' => $this->password,
            'amount' => '10.00',
            'currency' => 'RUB',
            'url_ok' => '/payment/ok',
            'url_fault' => '/payment/fail',
            'language' => 'ru',
            'paytype' => 'card',
            'customer_id' => $this->customer['id'],
            'customer_name' => $this->customer['name'],
            'customer_email' => $this->customer['email'],
            'customer_phone' => $this->customer['phone'],
            'timelimit' => 10
        );
    }

    /**
     * Test data array (getData)
     */
    public function testData()
    {
        $data = $this->request->getData();

        $this->assertEquals($data['order'], array(
            'shop_id' => $this->shopId,
            'number' => $this->orderId
        ));
        $this->assertEquals($data['cost'], array(
            'amount' => '10.00',
            'currency' => 'RUB'
        ));
        $this->assertEquals($data['postdata'], array(
            array('name' => 'Language', 'value' => 'ru'),
            array('name' => 'ReturnURLOk', 'value' => '/payment/ok'),
            array('name' => 'ReturnURLFault', 'value' => '/payment/fail'),
            array('name' => 'ChoosenCardType', 'value' => 'VI')
        ));
        $this->assertEquals($data['description'], array(
            'timelimit' => 10,
            'paytype' => 'card'
        ));
        $this->assertEquals($data['customer'], $this->customer);
    }

    /**
     * Test ability to add items to request
     */
    public function testItems()
    {
        $item = array(
            'typename' => 'good',
            'number' => mt_rand(1, 100),
            'amount' => array('amount' => '10.00', 'currency' => 'RUB'),
            'descr' => 'An item',
            'host' => ''
        );

        $this->request->addItem($item);

        $data = $this->request->getData();

        $this->assertEquals($data['description']['items'], array($item));
    }
}
