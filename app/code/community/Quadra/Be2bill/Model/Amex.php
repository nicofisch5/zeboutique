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
class Quadra_Be2bill_Model_Amex extends Quadra_Be2bill_Model_Abstract
{

    protected $_code = 'be2bill_amex';
    protected $_currentOperationType = "";
    protected $_canManageRecurringProfiles = false;
    protected $_canCapturePartial = true;
    protected $_canRefundInvoicePartial = false;
    protected $_allowCurrencyCode = array('EUR');

    /**
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInitializeNeeded()
    {
        if ($this->getConfigPaymentAction() == self::ACTION_AUTHORIZE)
            return false;
        return true;
    }

    /**
     * @return boolean
     */
    public function canManageRecurringProfiles()
    {
        return false;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_allowCurrencyCode))
            return false;
        return true;
    }

    public function getCheckoutFormFields()
    {
        $params = $this->getBaseParameters();
        $params['OPERATIONTYPE'] = $this->getOperationType();

        if (isset($params['CREATEALIAS']))
            $params['CREATEALIAS'] = "no";

        if (isset($params['3DSECURE']))
            $params['3DSECURE'] = "no";

        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        if (!$order->getId())
            Mage::throwException("An error occured. Order not exist!");

        /*$address = $order->getBillingAddress();
        $addressShipping = $order->getShippingAddress();

        $params['BILLINGFIRSTNAME'] = substr($address->getFirstname(), 0, 15);
        $params['BILLINGLASTNAME'] = substr($address->getLastname(), 0, 30);
        $params['BILLINGADDRESS'] = substr($address->getStreetFull(), 0, 19);
        if ($addressShipping) {
            $params['SHIPTOFIRSTNAME'] = substr($addressShipping->getFirstname(), 0, 15);
            $params['SHIPTOLASTNAME'] = substr($addressShipping->getLastname(), 0, 30);
            $params['SHIPTOADDRESS'] = substr($addressShipping->getStreetFull(), 0, 19);
        }*/

        $params['HASH'] = $this->generateHASH($params);

        $this->_debug($params);
        return $params;
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize($payment, $amount);
        if ($this->getConfigPaymentAction() == self::ACTION_AUTHORIZE) {
            $payment->setIsTransactionPending(1);
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);
        if ($this->isOneClickMode() && $this->getConfigPaymentAction() == self::ACTION_AUTHORIZE_CAPTURE) {
            //$this->oneclick($payment, $amount);
        } elseif ($this->getConfigPaymentAction() == self::ACTION_AUTHORIZE) {
            $this->_currentOperationType = self::OPERATION_TYPE_CAPTURE;
            $params = $this->getApi()->getBaseParameters();
            $params['TRANSACTIONID'] = $payment->getLastTransId();
            $params['ORDERID'] = $payment->getOrder()->getIncrementId();
            $params['AMOUNT'] = $this->formatAmount($amount);
            $params['OPERATIONTYPE'] = $this->getOperationType();
            $params['HASH'] = $this->generateHASH($params);
            $this->_debug($params);
            $service = $this->getApi();

            /* @var $response Quadra_Be2bill_Model_Api_Response */
            $response = $service->send($this->getOperationType(), $params);
            $this->_debug($response);
            if (!$response->isSuccess()) {
                Mage::logException(new Exception("Response: " . print_r($response->getData(), 1)));
                Mage::throwException("Error code: " . $response->getExecCode() . " " . $response->getMessage());
            } else {
                $payment->setIsPaid(1);
                $payment->setTransactionId($response->getTransactionId());
                $payment->setIsTransactionClosed(0);
            }
        }

        return $this;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getOperationType()
    {
        if ((int) $this->getQuote()->isNominal() && count($this->getQuote()->getAllVisibleItems()) > 0)
            return self::OPERATION_TYPE_AUTH;
        return parent::getOperationType();
    }

    /**
     * Refund money not allowed with amex
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        return $this;
    }

}
