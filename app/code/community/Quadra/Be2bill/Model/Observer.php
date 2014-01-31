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
class Quadra_Be2bill_Model_Observer
{

    public function notitifyCcExpDate($observer)
    {
        return $this;
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!Mage::getSingleton('customer/session')->isLoggedIn())
            return $this;

        if ($customer->getBe2billAlias() == "")
            return $this;

        if (!Mage::helper('be2bill')->checkIfCcExpDateIsValid($customer)) {
            $message = Mage::helper('be2bill')->__("Your One click profil is expired!");
            Mage::getSingleton('checkout/session')->addNotice($message);
        }

        return $this;
    }

    /**
     *
     * @param int $from
     * @param int $to
     * @return Mage_Sales_Model_Mysql4_Order_Collection
     */
    protected function getOrdersBySubDay($fromDay, $toDay)
    {
        $from = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());
        $from->subDay($fromDay);

        $to = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());
        $to->subDay($toDay);

        $statues = explode(",", Mage::getStoreConfig('payment/be2bill_standard/statues_order_to_clean'));

        $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('status', array("in" => $statues))
                ->addFieldTofilter('created_at', array("gteq" => $from->toString(Zend_Date::YEAR . "-" . Zend_Date::MONTH . "-" . Zend_Date::DAY . " " . Zend_Date::TIMES)))
                ->addFieldTofilter('created_at', array("lteq" => $to->toString(Zend_Date::YEAR . "-" . Zend_Date::MONTH . "-" . Zend_Date::DAY . " " . Zend_Date::TIMES)));

        return $orders;
    }

    public function addAdminNotification()
    {
        $limit_day = (int) Mage::getStoreConfig('payment/be2bill_standard/auth_validity_day');

        if (!$limit_day)
            return $this;

        $orders = $this->getOrdersBySubDay(5, 2);
        if ($orders->count()) {
            $inbox = Mage::getModel('adminnotification/inbox');
            $today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());
            $formatDate = Zend_Date::YEAR . "-" . Zend_Date::MONTH . "-" . Zend_Date::DAY . " " . Zend_Date::TIMES;
            $ordersData[] = array(
                'severity' => 2,
                'date_added' => $today->toString($formatDate),
                'title' => Mage::helper('be2bill')->__("You have %s order(s) to capture", $orders->count()),
                'description' => Mage::helper('be2bill')->__("They are orders in pending capture be2bill for at leat %s days", 5),
                'url' => Mage::getUrl('adminhtml/sales_order/'),
                'internal' => 1,
            );
            $inbox->parse(array_reverse($ordersData));
        }

        $orders = $this->getOrdersBySubDay(2, 1);
        if ($orders->count()) {
            $inbox = Mage::getModel('adminnotification/inbox');
            $today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());
            $formatDate = Zend_Date::YEAR . "-" . Zend_Date::MONTH . "-" . Zend_Date::DAY . " " . Zend_Date::TIMES;
            $ordersData[] = array(
                'severity' => 1,
                'date_added' => $today->toString($formatDate),
                'title' => Mage::helper('be2bill')->__("You have %s order(s) to capture", $orders->count()),
                'description' => Mage::helper('be2bill')->__("They are orders in pending capture be2bill for at leat %s days", 6),
                'url' => Mage::getUrl('adminhtml/sales_order/'),
                'internal' => 1,
            );
            $inbox->parse(array_reverse($ordersData));
        }

        return $this;
    }

    /**
     * Cancel orders in pending Be2bill because capture is limited to 7 days
     * @return Quadra_Be2bill_Model_Observer
     */
    public function cleanOrdersInPendingBe2bill()
    {
        $limit_day = (int) Mage::getStoreConfig('payment/be2bill_standard/auth_validity_day');

        if (!$limit_day)
            return $this;

        $orders = $this->getOrdersBySubDay(14, 7);

        foreach ($orders as $order) {
            try {
                /* @var $order Mage_Sales_Model_Order */
                if ($order->canCancel()) {
                    $order->cancel();
                    $order->addStatusToHistory(
                            $order->getStatus(),
                            // keep order status/state
                            Mage::helper('be2bill')->__("Order canceled by be2bill cron tab.")
                    );
                } else {
                    $order->addStatusToHistory(
                            $order->getStatus(),
                            // keep order status/state
                            Mage::helper('be2bill')->__("Order tried to cancel by cron tab but not successful.")
                    );
                }
                $order->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, "be2bill_error_cron.log");
                Mage::throwException($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Cancel orders stayed in pending because customer not validated be2bill form
     * @todo
     */
    public function cancelOrdersInPending()
    {
        $methodCodes = array('be2bill_standard' => 'be2bill/standard', 'be2bill_several' => 'be2bill/several');
        foreach ($methodCodes as $methodCode => $model) {

            $limitedTime = (int) Mage::getModel($model)->getConfigData('order_canceled_limited_time');
            if ($limitedTime <= 0)
                break;

            $date = Mage::app()->getLocale()->date();

            /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToSelect(array('entity_id', 'state'))
                    ->addAttributeToFilter('created_at', array('to' => ($date->subMinute($limitedTime)->toString('Y-MM-dd HH:mm:ss'))));
            Mage::log((string) $collection->getSelect(), null, "debug_clean_pending.log");
            /* @var $order Mage_Sales_Model_Order */
            foreach ($collection as $order) {

                if ($order->getPayment()->getMethod() == $methodCode) {
                    if ($order->canCancel() && $order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
                        try {
                            $order->cancel();
                            $order->addStatusToHistory($order->getStatus(),
                                    // keep order status/state
                                    Mage::helper('be2bill')->__("Order canceled automatically by cron because order is pending since %d minutes", $limitedTime));

                            $order->save();
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                }
            }
        }
        return $this;
    }

    public function setRedirectUrl($observer)
    {
        $quote = $observer->getQuote();
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        Mage::getSingleton('checkout/type_onepage')->getCheckout()->setRedirectUrl($redirectUrl);
        return $this;
    }

    public function submitRecurringProfiles()
    {
        $profiles = Mage::getModel('sales/recurring_profile')->getCollection()
                ->addFieldToFilter("state", Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE)
                ->addFieldToFilter("method_code", $this->getStandard()->getCode());

        $helper = Mage::helper('be2bill');
        foreach ($profiles as $profile) {
            $toSubmit = $helper->isRecurringTosubmit($profile);
            if ($toSubmit) {
                $this->getStandard()->subscription($profile);
            }
        }
        return $this;
    }

    /**
     * @return Quadra_Be2bill_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('be2bill/standard');
    }

}
