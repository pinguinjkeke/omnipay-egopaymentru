<?php

namespace Omnipay\EgopaymentRu\Contracts;

interface OrderItemContract
{
    /**
     * Product type. Must be one of [airticket, insurance, aezh, service, hotel, good, contract]
     *
     * @link https://tws.egopay.ru/docs/v2/#p-7.5
     * @return string
     */
    public function getOrderItemTypeName();

    /**
     * Item number (id) inside your application
     *
     * @return int
     */
    public function getOrderItemNumber();

    /**
     * Item cost must be an array i.e. ['cost' => 10.0, 'currency' => 'RUB']
     *
     * @return array
     */
    public function getOrderItemCost();

    /**
     * Distribution system
     * 
     * @return string 
     */
    public function getOrderItemHost();
}
