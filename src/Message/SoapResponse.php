<?php

namespace Omnipay\EgopaymentRu\Message;

use Omnipay\Common\Message\AbstractResponse;

class SoapResponse extends AbstractResponse
{
    /**
     * Constructor
     *
     * @param SoapAbstractRequest $request the initiating request.
     * @param mixed $data
     */
    public function __construct(SoapAbstractRequest $request, $data)
    {
        parent::__construct($request, $data);

        $this->data = is_string($data)
            ? $data
            : json_decode(json_encode($data->retval), true);
    }

    /**
     * Is the response successful?
     * In most cases if response is an array then it's successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return is_array($this->data);
    }
}
