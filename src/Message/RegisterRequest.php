<?php

namespace Omnipay\EgopaymentRu\Message;

use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\EgopaymentRu\Contracts\CustomerContract;
use Omnipay\EgopaymentRu\Contracts\OrderItemContract;
use SoapClient;

/**
 * Class RegisterRequest
 *
 * @package Omnipay\Egopayment
 * @link https://tws.egopay.ru/docs/v2/#p-3.1
 */
class RegisterRequest extends SoapAbstractRequest
{
    /**
     * Register modes
     * online - used in most cases (for online shops, etc)
     * offline - used for offline payments
     * simple - ???
     *
     * @var array
     */
    protected static $modes = [
        'online', 'offline', 'simple'
    ];

    /**
     * Items in order
     *
     * @var array
     */
    protected $items = [];

    /**
     * Get register mode
     *
     * @return mixed
     */
    public function getRegisterMode()
    {
        $registerMode = $this->getParameter('register_mode');

        return $registerMode ?: self::$modes[0];
    }

    /**
     * Set register mode
     *
     * @param int $mode
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setRegisterMode($mode = 0)
    {
        if (!in_array($mode, self::$modes, true)) {
            throw new RuntimeException("No \"{$mode}\" payment mode exists!");
        }

        return $this->setParameter('register_mode', $mode);
    }

    /**
     * Get customer's id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getParameter('customer_id');
    }

    /**
     * Set customer's id
     *
     * @param int $id
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCustomerId($id)
    {
        return $this->setParameter('customer_id', $id);
    }

    /**
     * Get customer's name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->getParameter('customer_name');
    }

    /**
     * Set customer's name
     *
     * @param string $name
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCustomerName($name)
    {
        return $this->setParameter('customer_name', $name);
    }

    /**
     * Get customer's email address
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getParameter('customer_email');
    }

    /**
     * Set customer's email address
     *
     * @param string $email
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCustomerEmail($email)
    {
        return $this->setParameter('customer_email', $email);
    }

    /**
     * Get customer's phone number
     *
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->getParameter('customer_phone');
    }

    /**
     * Set customer's phone number
     *
     * @param string $phone
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCustomerPhone($phone)
    {
        return $this->setParameter('customer_phone', $phone);
    }

    /**
     * Get time till user can pay the order in minutes
     *
     * @return int
     */
    public function getTimelimit()
    {
        return $this->getParameter('timelimit');
    }

    /**
     * Set time till user can pay the order in minutes
     *
     * @param int $minutes
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setTimelimit($minutes)
    {
        return $this->setParameter('timelimit', $minutes);
    }

    /**
     * @return mixed
     */
    public function getPaytype()
    {
        return $this->getParameter('paytype');
    }

    /**
     * @param $paytype
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setPaytype($paytype)
    {
        return $this->setParameter('paytype', $paytype);
    }

    /**
     * Get success url
     *
     * @return string
     */
    public function getUrlOk()
    {
        return $this->getParameter('url_ok');
    }

    /**
     * Set success url
     *
     * @param string $url
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setUrlOk($url)
    {
        return $this->setParameter('url_ok', $url);
    }

    /**
     * Get fault url
     *
     * @return string
     */
    public function getUrlFault()
    {
        return $this->getParameter('url_fault');
    }

    /**
     * Set fault url
     *
     * @param string $url
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setUrlFault($url)
    {
        $this->setParameter('url_fault', $url);
    }

    /**
     * Add item to order
     *
     * @param array|OrderItemContract $item
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function addItem($item)
    {
        if (is_array($item)) {
            $this->items[] = $item;

            return $this;
        } elseif ($item instanceof OrderItemContract) {
            $this->items[] = [
                'typename' => $item->getOrderItemTypeName(),
                'number' => $item->getOrderItemNumber(),
                'amount' => $item->getOrderItemCost(),
                'host' => $item->getOrderItemHost()
            ];

            return $this;
        }

        throw new RuntimeException('Item must be a type of array or implement the OrderItemContract');
    }

    /**
     * Sets order customer
     *
     * @param array|CustomerContract $customer
     * @return \Omnipay\Common\Message\AbstractRequest
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setCustomer($customer)
    {
        if (is_array($customer)) {
            return $this->setParameter('customer_id', $customer['id'])
                ->setParameter('customer_name', $customer['name'])
                ->setParameter('customer_email', $customer['email'])
                ->setParameter('customer_phone', $customer['phone']);
        } elseif ($customer instanceof CustomerContract) {
            return $this->setParameter('customer_id', $customer->getCustomerId())
                ->setParameter('customer_name', $customer->getCustomerName())
                ->setParameter('customer_email', $customer->getCustomerEmail())
                ->setParameter('customer_phone', $customer->getCustomerPhone());
        }

        throw new RuntimeException('Customer must be a type of array or implement CustomerContract');
    }
    
    /**
     * Runs SOAP request
     *
     * @param SoapClient $soapClient
     * @param $data
     * @return mixed response
     */
    protected function runTransaction(SoapClient $soapClient, $data)
    {
        $method = 'register_' . $this->getRegisterMode();

        return $soapClient->{$method}($data);
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
            'shop_id', 'number', 'amount', 'currency',
            'customer_id', 'customer_name', 'customer_email', 'customer_phone'
        );

        $data = array(
            'order' => array(
                'shop_id' => $this->getShopId(),
                'number' => $this->getOrderId()
            ),
            'cost' => array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCurrency()
            ),
            'description' => array(
                'timelimit' => $this->getTimelimit(),
                'paytype' => $this->getPaytype()
            ),
            'postdata' => array(
                array('name' => 'Language', 'value' => $this->getLanguage()),
                array('name' => 'ReturnURLOk', 'value' => $this->getUrlOk()),
                array('name' => 'ReturnURLFault', 'value' => $this->getUrlFault()),
                array('name' => 'ChoosenCardType', 'value' => 'VI')
            )
        );

        if ($this->getCustomerId()) {
            $data['customer'] = array(
                'id' => $this->getCustomerId(),
                'name' => $this->getCustomerName(),
                'email' => $this->getCustomerEmail(),
                'phone' => $this->getCustomerPhone()
            );
        }

        if (!empty($this->items)) {
            $data['description']['items'] = $this->items;
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
        $this->response = new RegisterResponse($this, parent::sendData($data));
        
        return $this->response;
    }
}