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
class Quadra_Be2bill_Block_Checkout_Oneclick_Info extends Mage_Checkout_Block_Cart_Abstract
{

    protected $_instance = null;

    /**
     * Available Carriers Instances
     * @var null|array
     */
    protected $_carriers = null;

    /**
     * Estimate Rates
     * @var array
     */
    protected $_rates = array();

    /**
     * Address Model
     *
     * @var array
     */
    protected $_address = array();

    public function getSubmitUrl()
    {
        return Mage::getUrl('be2bill/checkout_oneclick/infoPost', array('_secure' => true));
    }

    public function getMethodInstance()
    {
        if (empty($this->_instance)) {
            $this->_instance = $this->getQuote()->getPayment()->getMethodInstance();
        }
        return $this->_instance;
    }

    public function useCVV()
    {
        return $this->getMethodInstance()->getConfigData('use_cvv_oneclick');
    }

    public function getFormatedAmount()
    {
        $amount = $this->getQuote()->getBaseGrandTotal();
        return $this->getMethodInstance()->formatAmount($amount);
    }

    public function priceFormat($amount)
    {
        return Mage::app()->getStore()->getBaseCurrency()->format($amount, array(), true);
    }

    /**
     * Get Estimate Rates
     *
     * @return array
     */
    public function getEstimateRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }
        return $this->_rates;
    }

    /**
     * Get Address Model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    /**
     * Get Carrier Name
     *
     * @param string $carrierCode
     * @return mixed
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }
        return $carrierCode;
    }

}
