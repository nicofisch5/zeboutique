<?php

/**
 * 1997-2014 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <modules@quadra-informatique.fr>
 * @copyright 1997-2014 Quadra Informatique
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Quadra_Be2bill_Model_Api_Service
{

    const VERSION = "2.0";

    /**
     * @var Quadra_Be2bill_Model_Abstract
     */
    protected $_methodInstance = null;

    /**
     *
     * @var Zend_Http_Client
     */
    protected $_client = null;

    /**
     *
     * @var array
     */
    protected $_codeErrorToRetry = array();

    public function __construct($args)
    {
        $this->_methodInstance = $args['methodInstance'];
        $this->_codeErrorToRetry = array('5001', '5003');
    }

    /**
     * @return Quadra_Be2bill_Model_Abstract
     */
    public function getMethodInstance()
    {
        return $this->_methodInstance;
    }

    public function isTestMode()
    {
        return $this->getMethodInstance()->isTestMode();
    }

    /**
     * Get client HTTP
     * @return Zend_Http_Client
     */
    public function getClient()
    {
        if (is_null($this->_client)) {
            //adapter options
            $config = array('curloptions' => array(
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HEADER => false,
                    CURLOPT_RETURNTRANSFER => true),
            );
            try {
                //innitialize http client and adapter curl
                $adapter = Mage::getSingleton('be2bill/api_http_client_adapter_curl');
                $this->_client = new Zend_Http_Client();
                $this->_client->setAdapter($adapter);
                $adapter->setConfig($config);
            } catch (Exception $e) {
                Mage::throwException($e);
            }
        }

        return $this->_client;
    }

    /**
     * Send a request to Be2bill
     * @param string $methodToCall
     * @param array $params
     * @param string|null $uri
     *
     * @return Quadra_Be2bill_Model_Api_Response
     */
    public function send($methodToCall, $params, $uri = null)
    {
        if (is_null($uri)) {
            $uri[] = $this->getRestUrl();
            $uri[] = $this->getRestUrlHighDispo();
        } elseif (!is_array($uri)) {
            $uri = array($uri);
        }

        $this->getClient()->setParameterPost('method', $methodToCall);
        $this->getClient()->setParameterPost('params', $params);

        foreach ($uri as $restUrl) {

            $this->getClient()->setUri($restUrl);

            $response = $this->getClient()->request(Zend_Http_Client::POST);

            if ($response->getStatus() == 200) {
                $data = json_decode($response->getBody(), true);
                $response = Mage::getModel('be2bill/api_response')->setData($data);
                if (!in_array($response->getExecCode(), $this->_codeErrorToRetry))
                    return $response;
            }
        }

        Mage::throwException(Mage::helper('be2bill')->__('Unable to connect at be2bill servers'));
    }

    public function getBaseParameters()
    {
        $parameters = array();
        $parameters['IDENTIFIER'] = $this->getIdentifier();
        $parameters['DESCRIPTION'] = $this->getMethodInstance()->getDescription();
        $parameters['VERSION'] = self::VERSION;

        return $parameters;
    }

    public function generateHASH($params)
    {
        $pass = $this->getPassword();
        $finalString = $pass;
        ksort($params);
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as $index => $val) {
                    $finalString .= $key . '[' . $index . ']=' . $val . $pass;
                }
            } else {
                $finalString .= $key . "=" . $value . $pass;
            }
        }

        return hash('sha256', $finalString);
    }

    public function getPassword()
    {
        return $this->getConfigData('password');
    }

    public function getIdentifier()
    {
        return $this->getConfigData('identifier');
    }

    public function getRedirectUrl()
    {
        if ($this->isTestMode())
            return $this->getConfigData('uri_form_test');

        return $this->getConfigData('uri_form');
    }

    public function getRestUrl()
    {
        if ($this->isTestMode())
            return $this->getConfigData('uri_rest_test');

        return $this->getConfigData('uri_rest');
    }

    public function getRestUrlHighDispo()
    {
        if ($this->isTestMode())
            return $this->getConfigData('uri_rest_test');

        return $this->getConfigData('uri_rest_high_dispo');
    }

    public function getConfigData($path, $storeId = null) {
        $code = $this->getMethodInstance()->getCode();
        switch ($code) {
            case 'be2bill_amex' :
                return Mage::getStoreConfig('be2bill/be2bill_amex_api/' . $path, $storeId);
            case 'be2bill_paypal' :
                return Mage::getStoreConfig('be2bill/be2bill_paypal_api/' . $path, $storeId);
            default :
                return Mage::getStoreConfig('be2bill/be2bill_api/' . $path, $storeId);
        }
    }

}