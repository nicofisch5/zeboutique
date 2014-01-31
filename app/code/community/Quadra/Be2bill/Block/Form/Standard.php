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
class Quadra_Be2bill_Block_Form_Standard extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        $this->setTemplate('be2bill/form/standard.phtml')
                ->setMethodTitle('');
        parent::_construct();
    }

    public function getCustomerHasAlias()
    {
        return $this->getCustomer()->getBe2billAlias() != "";
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function ccExpDateIsValid()
    {
        return $this->helper('be2bill')->checkIfCcExpDateIsValid((int) Mage::getSingleton('customer/session')->getCustomerId());
    }

    public function oneClickIsAllowed()
    {
        $checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();

        if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || !$this->allowUseOneClick())
            return false;

        return true;
    }

    public function iframeIsAllowed()
    {
        return Mage::getStoreConfig('be2bill/be2bill_checkout_config/active_iframe');
    }

    public function useCVV()
    {
        return $this->getMethod()->getConfigData('use_cvv_oneclick');
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function allowUseOneClick()
    {
        return $this->getMethod()->getConfigData('allow_use_oneclick');
    }

    public function getMethodLabelAfterHtml()
    {
        return $this->getTitle();
    }

    public function getTitle()
    {
        switch ($this->getMethod()->getMethodName()) {
            case 'standard':
                $logo = 'logo-cb.png';
                break;
            case 'several':
                $logo = 'logo-3xcb.png';
                break;
            case 'amex':
                $logo = 'logo-amex.png';
                break;
            case 'paypal':
                $logo = 'logo-paypal.png';
                break;
            default:
                $logo = 'visa.png';
        }

        return '<img class="v-middle" src="' . $this->getSkinUrl('images/be2bill/' . $logo) . '" /><span style="margin:0 5px">' . $this->getMethod()->getTitle() . '</span>';
    }

}