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
class Quadra_Be2bill_Checkout_OneclickController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');
    protected $_paymentMethod = '';

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack()
    {
        $redirectUrl = $this->getRequest()->getServer('HTTP_REFERER');
        $this->getResponse()->setRedirect($redirectUrl);
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    protected function _initOrder()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
        }

        $checkout = Mage::getSingleton('checkout/type_onepage');
        $checkout->initCheckout();
        $checkout->getQuote()->collectTotals()->save();

        // set addresses
        $addressId = $this->getRequest()->getParams('shipping_address_id', 0);
        if ($addressId > 0) {
            $checkout->saveBilling(array(), $addressId);
            $checkout->saveShipping(array(), $addressId);
        }

        // get shipping address
        $shippingAddress = $checkout->getQuote()->getShippingAddress();

        // get shipping method code
        $shippingRateGroups = $shippingAddress->getGroupedAllShippingRates();
        $shippingMethodCode = '';
        if ($this->getRequest()->getParam('use_default_shipping_method')) {
            foreach ($shippingRateGroups as $_rates) {
                foreach ($_rates as $_rate) {
                    $shippingMethodCode = $_rate->getCode();
                    break;
                }
                break;
            }
        }

        $checkout->saveShippingMethod($shippingMethodCode);
        $checkout->getQuote()
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();

        $paymentData = array(
            'method' => 'be2bill_' . $this->_paymentMethod,
            'oneclick' => (Mage::helper('customer')->getCurrentCustomer()->getBe2billAlias()) ? array('be2bill_' . $this->_paymentMethod => 'use_oneclick') : '',
            'cvv_oneclick' => $this->getRequest()->getParam('cvv_oneclick', '')
        );

        $checkout->savePayment($paymentData);

        return $checkout;
    }

    /**
     * Create order
     * @return string
     */
    protected function _createOrder()
    {
        $checkout = $this->_initOrder();
        try {
            $checkout->saveOrder();
            $redirectUrl = Mage::getUrl('be2bill/' . $this->_paymentMethod . '/oneclick', array('_secure' => true));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($checkout->getQuote(), $e->getMessage());
            Mage::getSingleton('catalog/session')->addError($e->getMessage());
            $redirectUrl = $this->getRequest()->getServer('HTTP_REFERER');
        }

        return $redirectUrl;
    }

    /**
     * Order a product in one click
     */
    public function orderProductAction()
    {
        $this->_getSession()->clear();
        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $this->_paymentMethod = $this->getRequest()->getParam('method');

        $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->save();
        $cart->setQuote($quote);

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if ($this->_paymentMethod == 'several' ||
                    !$params['use_default_shipping_method'] ||
                    Mage::getSingleton('be2bill/' . $this->_paymentMethod)->getConfigData('use_cvv_oneclick')) {
                $checkout = $this->_initOrder();
                $this->getResponse()->setRedirect(Mage::getUrl('be2bill/checkout_oneclick/info', array('quote_id' => $checkout->getQuote()->getId(), '_secure' => true)));
            } else {
                $redirectUrl = $this->_createOrder();
                $this->getResponse()->setRedirect($redirectUrl);
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    public function infoAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function infoPostAction()
    {
        $checkout = Mage::getSingleton('checkout/type_onepage');
        $params = $this->getRequest()->getParams();

        if (isset($params['payment'])) {
            $cvv = $params['payment']['cvv_oneclick'];
            $method = $checkout->getQuote()->getPayment()->getMethod();
            $paymentData = array(
                'method' => $method,
                'oneclick' => (Mage::helper('customer')->getCurrentCustomer()->getBe2billAlias()) ? array($method => 'use_oneclick') : '',
                'cvv_oneclick' => ($cvv) ? $cvv : ''
            );

            $checkout->savePayment($paymentData);
        }

        $checkout->getQuote()
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();

        try {
            $checkout->saveOrder();
            $paymentMethod = explode('_', $checkout->getQuote()->getPayment()->getMethod());
            $redirectUrl = Mage::getUrl(implode('/', $paymentMethod) . '/oneclick', array('_secure' => true));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($checkout->getQuote(), $e->getMessage());
            Mage::getSingleton('catalog/session')->addError($e->getMessage());
            $redirectUrl = $this->getRequest()->getServer('HTTP_REFERER');
        }

        $this->getResponse()->setRedirect($redirectUrl);
    }

    public function saveShippingMethodAction()
    {
        $shippingMethodCode = $this->getRequest()->getParam('estimate_method');

        $checkout = Mage::getSingleton('checkout/type_onepage');
        $checkout->saveShippingMethod($shippingMethodCode);
        $checkout->getQuote()
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();

        $block = $this->getLayout()->createBlock('be2bill/checkout_oneclick_info', 'be2bill.checkout.oneclick.info.review')
                ->setTemplate('be2bill/checkout/oneclick/info/review.phtml');
        $block->addItemRender('simple', 'checkout/cart_item_renderer', 'be2bill/checkout/cart/item/default.phtml');
        $block->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'be2bill/checkout/cart/item/default.phtml');
        $block->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'be2bill/checkout/cart/item/default.phtml');

        $totals = $this->getLayout()->createBlock('checkout/cart_totals', 'checkout.cart.totals')
                ->setTemplate('be2bill/checkout/cart/totals.phtml');

        $block->append($totals, 'totals');

        return $this->getResponse()->setBody($block->toHtml());
    }

}
