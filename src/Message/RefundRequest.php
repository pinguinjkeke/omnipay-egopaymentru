<?php

namespace Omnipay\EgopayRu\Message;

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\EgopayRu\Contracts\OrderItemContract;
use SoapClient;

class RefundRequest extends SoapAbstractRequest
{
    /**
     * Items array
     * 
     * @var array
     */
    protected $items = array();
    
    /**
     * Get payment id you received from gate
     *
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->getParameter('payment_id');
    }

    /**
     * Set payment id you received from gate
     *
     * @param mixed $paymentId
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setPaymentId($paymentId)
    {
        return $this->setParameter('payment_id', $paymentId);
    }

    /**
     * Get unique refund id
     * 
     * @return string
     */
    public function getTxnId()
    {
        return $this->getParameter('txn_id');
    }

    /**
     * Set unique refund id
     *
     * @param string $txnId
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setTxnId($txnId)
    {
        return $this->setParameter('txn_id', $txnId);
    }

    /**
     * Add item for partial refund
     * 
     * @param array|OrderItemContract $item
     * @return $this
     */
    public function addItem($item)
    {
        if (is_array($item)) {
            $this->items[] = $item;
            
            return $this;
        } elseif ($item instanceof OrderItemContract) {
            // TODO implement item 'descr' field
            $this->items[] = array(
                'id' => $item->getOrderItemNumber(),
                'amount' => array(
                    'amount' => $item->getOrderItemCost(),
                    'currency' => $this->getCurrency()
                )
            );
            
            return $this;
        }
    }

    /**
     * Runs SOAP request
     *
     * @param SoapClient $soapClient
     * @param $data
     * @return mixed
     */
    protected function runTransaction(SoapClient $soapClient, $data)
    {
        return $soapClient->refund($data);
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
        $this->validate(
            'shop_id', 'order_id', 'user', 'password',
            'payment_id', 'amount', 'currency', 'txn_id'
        );
        
        $data = array(
            'order' => array(
                'shop_id' => $this->getShopId(),
                'number' => $this->getOrderId()
            ),
            'payment_id' => $this->getPaymentId(),
            'cost' => array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCurrency()
            ),
            'txn_id' => $this->getTxnId()
        );
        
        if (!empty($this->items)) {
            $data['items'] = $this->items;
        }

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $this->response = new RefundResponse($this, parent::sendData($data));

        return $this->response;
    }
}
