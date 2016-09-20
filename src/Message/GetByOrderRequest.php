<?php

namespace Omnipay\EgopayRu\Message;

use Omnipay\Common\Message\ResponseInterface;
use SoapClient;

/**
 * Order status from gateway v4 instead of v2 because of improved API
 * with card tokens, etc
 */
class GetByOrderRequest extends SoapAbstractRequest
{
    /**
     * Runs SOAP request
     *
     * @param SoapClient $soapClient
     * @param $data
     * @return mixed
     */
    protected function runTransaction(SoapClient $soapClient, $data)
    {
        return $soapClient->get_by_order($data);
    }

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('shop_id', 'order_id', 'user', 'password');
        
        return array(
            'order' => array(
                'shop_id' => $this->getShopId(),
                'number' => $this->getOrderId()
            )
        );
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $this->response = new GetByOrderResponse($this, parent::sendData($data));

        return $this->response;
    }
}
