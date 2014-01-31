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
class Quadra_Be2bill_Helper_Data extends Mage_Core_Helper_Data
{

    protected $_methods = array(
        'be2bill_standard',
        'be2bill_several',
        'be2bill_amex',
        'be2bill_paypal'
    );

    /**
     * Get methods
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Get template for button in order review page if be2bill method was selected
     *
     * @param string $name template name
     * @param string $block buttons block name
     * @return string
     */
    public function getReviewButtonTemplate($name, $block)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote && Mage::getStoreConfig('be2bill/be2bill_checkout_config/active_iframe')) {
            $payment = $quote->getPayment();
            if ($payment && in_array($payment->getMethod(), $this->_methods)) {
                return $name;
            }
        }

        if ($blockObject = Mage::getSingleton('core/layout')->getBlock($block)) {
            return $blockObject->getTemplate();
        }

        return '';
    }

    public function reAddToCart($incrementId)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

        if ($order->getId()) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                }
            }
        }

        $cart->save();
    }

    public function checkIfCcExpDateIsValid($customer)
    {
        if (is_int($customer))
            $customer = Mage::getModel('customer/customer')->load($customer);

        $expDate = $customer->getBe2billCcExpDate();
        $alias = $customer->getBe2billAlias();
        if (!empty($expDate) && !empty($alias)) {
            list($expMonth, $expYear) = explode("-", $expDate);
            $today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());

            $currentYear = (int) $today->getYear()->toString("YY");
            $currentMonth = (int) $today->getMonth()->toString("MM");

            if ($currentYear > (int) $expYear)
                return false;

            if ($currentYear == (int) $expYear && $currentMonth > (int) $expMonth)
                return false;

            return true;
        }

        return false;
    }

    public function isRecurringTosubmit(Mage_Sales_Model_Recurring_Profile $profile)
    {
        $orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('entity_id', array('in' => $profile->getChildOrderIds()));

        $startDate = new Zend_Date($profile->getStartDatetime());
        $todayDate = new Zend_Date();

        if ($startDate->compare($todayDate) <= 0 && $orders->count() < 1) {
            return true;
        }

        if ($orders->count() > 0) {
            $currentNbCycle = $orders->count();
            $maxCycles = $profile->getPeriodMaxCycles();
            $periodFrequency = $profile->getPeriodFrequency();

            if (!empty($maxCycles) && $orders->count() == ($periodFrequency * $maxCycles)) {
                return false;
            }

            $lastOrder = $orders->getLastItem();
            $lastDate = new Zend_Date($lastOrder->getCreatedAt());

            switch ($profile->getPeriodUnit()) {
                case Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_MONTH:
                    if ($lastDate->addMonth(1)->getDate()->compare($todayDate->getDate()) <= 0)
                        return true;
                    break;
                case Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_DAY:
                    if ($lastDate->addDay(1)->getDate()->compare($todayDate->getDate()) <= 0)
                        return true;
                    break;
                case Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH:
                    if ($lastDate->addDay(15)->getDate()->compare($todayDate->getDate()) <= 0)
                        return true;
                    break;
                case Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_WEEK:
                    if ($lastDate->addWeek(7)->getDate()->compare($todayDate->getDate()) <= 0)
                        return true;
                    break;
                case Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_YEAR:
                    if ($lastDate->addYear(1)->getDate()->compare($todayDate->getDate()) <= 0)
                        return true;
                    break;
                default:
                    break;
            }
        }

        return false;
    }

    public function isBe2billServer()
    {
        $allowedRangeIps = explode(",", Mage::getStoreConfig('payment/be2bill_standard/allow_range_ips'));
        /* @var $_helperIp Quadra_Be2bill_Helper_Ip */
        $_helperIp = Mage::helper('be2bill/ip');
        foreach ($allowedRangeIps as $range) {
            list($ip, $mask) = explode("/", $range);
            if ($_helperIp->checkIfRemoteIpIsInRange($ip, $mask) === true)
                return true;
        }

        return false;
    }

    /**
     * @deprecated
     * @param int $storeId
     */
    public function isBe2billServerOld($storeId = null)
    {
        $allow = true;

        $allowedIps = Mage::getStoreConfig('payment/be2bill_standard/allow_ips', $storeId);
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                $allow = false;
            }
        }

        return $allow;
    }

}
