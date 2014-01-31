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
class Quadra_Be2bill_Model_Standard
    extends Quadra_Be2bill_Model_Abstract
    implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{

    protected $_code = 'be2bill_standard';
    protected $_currentOperationType = "";
    protected $_canManageRecurringProfiles = true;
    protected $_canCapturePartial = true;
    protected $_canRefundInvoicePartial = true;
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
        return parent::canManageRecurringProfiles() && $this->getConfigData('allow_recurring_profile');
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

        if ($params['CREATEALIAS'] == 'no' && $this->getConfigData('allow_use_oneclick')) {
            unset($params['CREATEALIAS']);
            $params['DISPLAYCREATEALIAS'] = 'yes';
        }

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
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        return $this;
    }

    /**
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info $paymentInfo
     */
    public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo)
    {
        if ($this->isOneClickMode()) {
            $customer = Mage::getSingleton('customer/customer')->load($profile->getCustomerId());
            if ($customer->getId()) {
                $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
                $referenceId = $customer->getBe2billAlias() . "-" . $profile->getId();
                $profile->setAdditionalInfo(array("alias" => $customer->getBe2billAlias()));
                $profile->setReferenceId($referenceId);
            }
        }
        return $this;
    }

    /**
     * @param unknown_type $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result)
    {
        return $this;
    }

    /**
     *
     */
    public function canGetRecurringProfileDetails()
    {
        return false;
    }

    /**
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        // TODO: Auto-generated method stub
    }

    /**
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile)
    {
        return $this;
    }

}
