<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Zeboutique
 * @package     Zeboutique_Paypal
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Paypal
 *
 * @category    Zeboutique
 * @package     Zeboutique_Paypal
 * @author      Zeboutique
 */
class Zeboutique_Paypal_Model_Ipn extends Mage_Paypal_Model_Ipn {

    /**
     * IPN workflow implementation
     * Everything should be added to order comments. In positive processing cases customer will get email notifications.
     * Admin will be notified on errors.
     */
    protected function _processOrder()
    {
        $this->_order = null;
        $this->_getOrder();

        $this->_info = Mage::getSingleton('paypal/info');
        try {
            // handle payment_status
            $paymentStatus = $this->_filterPaymentStatus($this->_request['payment_status']);

            switch ($paymentStatus) {
                // paid
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED:
                    $this->_registerPaymentCapture();
                    break;

                // the holded payment was denied on paypal side
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_DENIED:
                    $this->_registerPaymentDenial();
                    break;

                // customer attempted to pay via bank account, but failed
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_FAILED:
                    // cancel order
                    $this->_registerPaymentFailure();
                    break;

                // refund forced by PayPal
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED: // break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_UNREVERSED: // or returned back :)
                    $this->_registerPaymentReversal();
                    break;

                // refund by merchant on PayPal side
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED:
                    $this->_order->addStatusHistoryComment(
                        Mage::helper('paypal')->__('Refund canceled by Zeboutique.')
                    )
                        ->setIsCustomerNotified(true)
                        ->save();
                    break;

                // payment was obtained, but money were not captured yet
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING:
                    $this->_registerPaymentPending();
                    break;

                // MassPayments success
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_PROCESSED:
                    $this->_registerMasspaymentsSuccess();
                    break;

                // authorization expire/void
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_EXPIRED: // break is intentionally omitted
                case Mage_Paypal_Model_Info::PAYMENTSTATUS_VOIDED:
                    $this->_registerPaymentVoid();
                    break;

                default:
                    throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
            }
        } catch (Mage_Core_Exception $e) {
            $comment = $this->_createIpnComment(Mage::helper('paypal')->__('Note: %s', $e->getMessage()), true);
            $comment->save();
            throw $e;
        }
    }
}