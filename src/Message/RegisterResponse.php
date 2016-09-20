<?php

namespace Omnipay\EgopayRu\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

class RegisterResponse extends SoapResponse implements RedirectResponseInterface
{
    /**
     * Does the response require a redirect?
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return true;
    }

    /**
     * Gets the redirect target url.
     * 
     * @return string
     */
    public function getRedirectUrl()
    {
        return "{$this->data['redirect_url']}?session={$this->data['session']}";
    }

    /**
     * Gateway Reference
     *
     * @return string A reference provided by the gateway to represent this transaction
     */
    public function getTransactionReference()
    {
        return $this->data['session'];
    }

    /**
     * Get the required redirect method (either GET or POST).
     * 
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     * 
     * @return array|bool
     */
    public function getRedirectData()
    {
        return false;
    }
}
