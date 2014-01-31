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
class Quadra_Be2bill_Block_Checkout_Oneclick_Product extends Mage_Catalog_Block_Product_View
{

    /**
     * Retrieves url for form submitting:
     * some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = array())
    {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : array();
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $params['product'] = $product->getId();
            $submitUrl = $this->getUrl('be2bill/checkout_oneclick/orderProduct', array_merge($params, $additional));
        }
        return $submitUrl;
    }

    public function canOneclick()
    {
        $helper = Mage::helper('customer');

        if (!Mage::getStoreConfig('be2bill/be2bill_oneclick_config/active_product_view_payment') || !Mage::getStoreConfig('payment/be2bill_standard/active') || !$helper->isLoggedIn() || !$helper->customerHasAddresses())
            return false;

        return true;
    }

    public function canSeveralOneclick()
    {
        $helper = Mage::helper('customer');

        if (!Mage::getStoreConfig('be2bill/be2bill_oneclick_config/active_product_view_payment') || !Mage::getStoreConfig('payment/be2bill_several/active') || !$this->getProduct()->getBe2billEnableOcSevPayment() || !$helper->isLoggedIn() || !$helper->customerHasAddresses())
            return false;

        return true;
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            if ($type == 'billing') {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
            } else {
                $address = $this->getCustomer()->getPrimaryShippingAddress();
            }
            if ($address) {
                $addressId = $address->getId();
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                    ->setName($type . '_address_id')
                    ->setId($type . '-address-select')
                    ->setClass('address-select')
                    ->setValue($addressId)
                    ->setOptions($options);

            return $select->getHtml();
        }
        return $this->__('Please, <a href="%s">add an address</a>.', Mage::getUrl('customer/address/new/', array('_secure' => true)));
    }

    public function getShippingMethodSelect()
    {
        if ($this->isCustomerLoggedIn() && $this->customerHasAddresses()) {
            $options = array();
            $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();

            foreach ($carriers as $carrierCode => $carrierModel) {
                if (!$carrierModel->isActive()) {
                    continue;
                }

                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }

                $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title');
                $options[] = array('value' => $carrierCode, 'label' => $carrierTitle);
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                    ->setName('shipping_method_code')
                    ->setId('shipping-method-code-select')
                    ->setClass('select')
                    ->setValue()
                    ->setOptions($options);

            return $select->getHtml();
        }
        return '';
    }

}
