<?php

namespace Omnipay\EgopayRu\Message;

class ConfirmRequestTest extends AbstractRequestTest
{
    /**
     * Request class name
     *
     * @return string
     */
    protected function getRequestClassName()
    {
        return 'ConfirmRequest';
    }

    /**
     * Request parameters
     *
     * @return array
     */
    protected function getRequestParameters()
    {
        return array(
            'shop_id' => '16531',
            'order_id' => 456,
            'user' => 'hello',
            'password' => 'world',
            'amount' => '10.00',
            'currency' => 'RUB',
            'txn_id' => 'confirm1_123456'
        );
    }

    /**
     * Test data array (getData)
     */
    public function testData()
    {
        $data = $this->request->getData();

        $this->assertEquals($data['order'], array('shop_id' => '16531', 'number' => 456));
        $this->assertEquals($data['cost'], array('amount' => '10.00', 'currency' => 'RUB'));
        $this->assertEquals($data['txn_id'], 'confirm1_123456');
    }
}
