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
abstract class Quadra_Be2bill_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{

    const VERSION = "2.0";
    const OPERATION_TYPE_PAYMENT = 'payment';
    const OPERATION_TYPE_AUTH = 'authorization';
    const OPERATION_TYPE_CAPTURE = 'capture';
    const OPERATION_TYPE_REFUND = 'refund';
    const OPERATION_TYPE_ONECLICK = 'oneclick';
    const OPERATION_TYPE_SUBSCRIPTION = 'subscription';
    const OPTION_3DSECURE_DISABLE = 0;
    const OPTION_3DSECURE_FULL = 1;
    const OPTION_3DSECURE_SELECTIVE = 2;

    protected $_currentOperationType = "";
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = false;
    protected $_canCapture = true;
    protected $_canUseInternal = false;
    protected $_formBlockType = 'be2bill/form_standard';
    protected $_infoBlockType = 'be2bill/info_standard';
    protected $_debugReplacePrivateDataKeys = array();

    public function getCheckoutFormFields()
    {
        throw new Exception("Method " . __METHOD__ . " must be implemented!");
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Purchaseorder
     */
    public function assignData($data)
    {
        parent::assignData($data);

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $oneclick = $data->getOneclick();

        $this->getInfoInstance()->setAdditionalInformation('create_oneclick', $oneclick[$this->getCode()] == "create_oneclick" ? "yes" : "no");
        $this->getInfoInstance()->setAdditionalInformation('use_oneclick', $oneclick[$this->getCode()] == "use_oneclick" ? "yes" : "no");
        $this->getInfoInstance()->setAdditionalInformation('cvv_oneclick', trim($data->getCvvOneclick()));
        return $this;
    }

    /**
     * Get be2bill api service
     *
     * @return Quadra_Be2bill_Model_Api_Service
     */
    public function getApi()
    {
        return Mage::getSingleton('be2bill/api_service', array('methodInstance' => $this));
    }

    /**
     * Get be2bill session namespace
     *
     * @return Quadra_Be2bill_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('be2bill/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getOrderPlaceRedirectUrl()
    {
        if ($this->isOneClickMode() && $this->getQuote()->isNominal())
            return false;

        if ($this->getMethodName() != 'paypal' && Mage::getStoreConfig('be2bill/be2bill_checkout_config/active_iframe'))
            return 'javascript:void(0)';

        if ($this->isOneClickMode())
            return Mage::getUrl('be2bill/' . $this->getMethodName() . '/oneclick', array('_secure' => true));

        return Mage::getUrl('be2bill/' . $this->getMethodName() . '/redirect', array('_secure' => true));
    }

    public function getMethodName()
    {
        $tabMethod = explode("_", $this->getCode());
        return $tabMethod[1];
    }

    public function isOneClickMode()
    {
        if ($this->getInfoInstance()->getAdditionalInformation('use_oneclick') == 'yes')
            return true;

        return false;
    }

    public function getCardCVV($payment = null)
    {
        if (is_null($payment))
            return $this->getInfoInstance()->getAdditionalInformation('cvv_oneclick');

        return $payment->getAdditionalInformation('cvv_oneclick');
    }

    public function getRedirectUrl()
    {
        return $this->getApi()->getRedirectUrl();
    }

    public function use3dSecure($order)
    {
        // 3D Secure is inactive
        if ($this->getConfigData('use_3dsecure') == self::OPTION_3DSECURE_DISABLE)
            return '';

        // 3D Secure is full active
        if ($this->getConfigData('use_3dsecure') == self::OPTION_3DSECURE_FULL)
            return "3DSECURE:OUI;";

        // Amount is upper to minimum order total required for 3D Secure
        $amount = $this->formatAmount($order->getBaseGrandTotal());
        $minOrder = $this->formatAmount($this->getConfigData('min_order_total_3dsecure'));

        if ($minOrder && $minOrder <= $amount)
            return "3DSECURE:SELECTIF;AMOUNT:{$minOrder};";

        // Shipping address country is in 3D Secure country list
        $shippingAddressCountry = $order->getShippingAddress()->getCountry();
        $allowedCountries = explode(',', $this->getConfigData('specificcountry_3dsecure'));

        if (in_array($shippingAddressCountry, $allowedCountries))
            return "3DSECURE:SELECTIF;COUNTRY:{$shippingAddressCountry};";

        // Shipping address country is France and postcode is in 3D Secure postcode list
        if ($shippingAddressCountry == 'FR') {
            $postcode = $order->getShippingAddress()->getPostcode();
            $allowedPostcode = explode(',', $this->getConfigData('postcode_3dsecure'));
            $find = false;
            foreach ($allowedPostcode as $pc) {
                if (strlen($pc) == 5) {
                    if ($pc == $postcode) {
                        $find = $pc;
                        break;
                    }
                } else {
                    if (preg_match("/^{$pc}/", $postcode)) {
                        $find = $pc;
                        break;
                    }
                }
            }
            if ($find)
                return "3DSECURE:SELECTIF;ZIPCODE:{$find};";
        }

        // Shipping method is in 3D Secure shipping method list
        $shippingMethod = $order->getShippingMethod();
        $allowedShippingMethods = explode(',', $this->getConfigData('shipping_methods_3dsecure'));

        if (in_array($shippingMethod, $allowedShippingMethods))
            return "yes";

        // Product enable 3D Secure
        $find = false;
        $categoryList = array();
        foreach ($order->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ((int) $product->getData('be2bill_3dsecure')) {
                $find = $product->getSku();
                break;
            } else {
                $categoryList[$product->getId()] = $product->getCategoryIds();
            }
        }
        if ($find)
            return "3DSECURE:SELECTIF;PRODUCT:{$find};";

        // Category enable 3D Secure
        $find = false;
        foreach ($categoryList as $categoryIds) {
            foreach ($categoryIds as $id) {
                $category = Mage::getModel('catalog/category')->load($id);
                if ($category->getId()) {
                    if ((int) $category->getData('be2bill_3dsecure')) {
                        $find = strtoupper($category->getUrlKey());
                        break;
                    }
                }
            }
            if ($find)
                break;
        }

        if ($find)
            return "3DSECURE:SELECTIF;CATEGORY:{$find};";

        return '';
    }

    public function isTestMode()
    {
        return $this->getConfigData("is_test_mode");
    }

    public function generateHASH($params)
    {
        return $this->getApi()->generateHASH($params);
    }

    public function formatAmount($amount)
    {
        return $amount * 100;
    }

    public function unFormatAmount($amount)
    {
        return $amount / 100;
    }

    public function getBaseParameters()
    {
        $parameters = $this->getApi()->getBaseParameters();
        $customerId = "";
        $orderIncrementId = "";
        $grandTotal = 0;
        $createAlias = "no";

        if (($profileIds = $this->getCheckout()->getLastRecurringProfileIds())) {
            if (is_array($profileIds)) {
                return $this->getParametersForCreateAliasWithoutOrder($profileIds, " - (Alias creation for recurring payment)");
            }
            Mage::throwException("An error occured. Profile Ids not present!");
        } else {
            /* @var $order Mage_Sales_Model_Order */
            $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

            if (!$order->getId())
                Mage::throwException("An error occured. Order not exist!");

            $address = $order->getBillingAddress();
            $addressShipping = $order->getShippingAddress();
            $customerId = $order->getCustomerId();
            $grandTotal = $order->getBaseGrandTotal();
            $createAlias = $order->getPayment()->getAdditionalInformation('create_oneclick');
        }

        $parameters['CLIENTIDENT'] = is_numeric($customerId) ? $customerId : $address->getEmail();
        $parameters['ORDERID'] = $orderIncrementId;
        $parameters['AMOUNT'] = $this->formatAmount($grandTotal);
        $parameters['CLIENTEMAIL'] = $address->getEmail();
        //$parameters['FIRSTNAME'] = $address->getFirstname();
        //$parameters['LASTNAME'] = $address->getLastname();
        $parameters['CARDFULLNAME'] = $address->getFirstname() . " " . $address->getLastname();

        $customer = Mage::getModel('customer/customer')->load($customerId);
        if ($customer->getDob() != "") {
            $dateTimePart = explode(" ", $customer->getDob());
            if (isset($dateTimePart[0]))
                $parameters['CLIENTDOB'] = $dateTimePart[0];
        }

        //$parameters['CLIENTADDRESS'] = substr($address->format("oneline"), 0, 510);

        /* Billing address fields */
        //$parameters['BILLINGFIRSTNAME']= substr($address->getFirstname(),0,15);
        //$parameters['BILLINGLASTNAME']= substr($address->getLastname(),0,30);
        //$parameters['BILLINGADDRESS']= substr($address->getStreetFull(),0,19);
        $parameters['BILLINGCOUNTRY'] = substr($address->getCountryId(), 0, 2);
        $parameters['BILLINGPOSTALCODE'] = substr($address->getPostcode(), 0, 9);
        $parameters['BILLINGPHONE'] = substr($address->getTelephone(), 0, 10);

        /* shipping address fields */
        if ($addressShipping) {
            //$parameters['SHIPTOFIRSTNAME']= substr($addressShipping->getFirstname(),0,15);
            //$parameters['SHIPTOLASTNAME']= substr($addressShipping->getLastname(),0,30);
            //$parameters['SHIPTOADDRESS']= substr($addressShipping->getStreetFull(),0,19);
            $parameters['SHIPTOCOUNTRY'] = substr($addressShipping->getCountryId(), 0, 2);
            $parameters['SHIPTOPOSTALCODE'] = substr($addressShipping->getPostcode(), 0, 9);
            $parameters['SHIPTOPHONE'] = substr($addressShipping->getTelephone(), 0, 10);
        }

        $data3dSecure = $this->use3dSecure($order);
        if (strlen($data3dSecure) > 0) {
            $parameters['3DSECURE'] = "yes";
            $parameters['EXTRADATA'] = $data3dSecure;
        }
        else
            $parameters['3DSECURE'] = "no";
        $parameters['CREATEALIAS'] = $createAlias;

        if ($createAlias == 'yes')
            $parameters['DESCRIPTION'] = $parameters['DESCRIPTION'] . " - (Alias created)";

        $parameters['HIDECLIENTEMAIL'] = $this->getConfigData('hide_client_email') ? 'yes' : 'no';
        $parameters['HIDECARDFULLNAME'] = $this->getConfigData('hide_card_fullname') ? 'yes' : 'no';

        $parameters['LANGUAGE'] = strpos(Mage::app()->getLocale()->getLocaleCode(), "fr") !== false ? "fr" : 'en';
        //$parameters['EXTRADATA'] = "";
        //$parameters['USETEMPLATE'] = "";

        return $parameters;
    }

    public function getParametersForCreateAliasWithoutOrder(array $profileIds, $desc = ' - (Alias creation)')
    {
        $this->_currentOperationType = self::OPERATION_TYPE_PAYMENT;
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $parameters = $this->getApi()->getBaseParameters();

        $parameters['CLIENTIDENT'] = $customer->getId();
        //if(is_null($profileId))
        //$orderId = uniqid('create-alias-');
        $orderId = 'create-recurring';
        $amount = 0;
        foreach ($profileIds as $profileId) {
            /* @var $profile Mage_Sales_Model_Recurring_Profile */
            $profile = Mage::getModel('sales/recurring_profile')->load($profileId);
            $orderId .= "-" . $profileId;
            $amount += $this->getAmountFromProfile($profile);
            break; //because only one nominal item in cart is authorized and be2bill not manage many profiles
        }

        $parameters['ORDERID'] = $orderId;
        $parameters['AMOUNT'] = $this->formatAmount($amount);
        $parameters['CLIENTEMAIL'] = $customer->getEmail();
        $parameters['FIRSTNAME'] = $customer->getFirstname();
        $parameters['LASTNAME'] = $customer->getLastname();
        $parameters['3DSECURE'] = $this->getConfigData('use_3dsecure') ? "yes" : "no";
        $parameters['CREATEALIAS'] = "yes";
        $parameters['DESCRIPTION'] = $parameters['DESCRIPTION'] . $desc;
        $parameters['HIDECLIENTEMAIL'] = $this->getConfigData('hide_client_email') ? 'yes' : 'no';
        $parameters['HIDECARDFULLNAME'] = $this->getConfigData('hide_card_fullname') ? 'yes' : 'no';

        return $parameters;
    }

    /**
     * Add method to calculate amount from recurring profile
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @return int $amount
     * */
    public function getAmountFromProfile(Mage_Sales_Model_Recurring_Profile $profile)
    {
        $amount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();

        if ($this->isInitialProfileOrder($profile))
            $amount += $profile->getInitAmount();

        return $amount;
    }

    protected function isInitialProfileOrder(Mage_Sales_Model_Recurring_Profile $profile)
    {
        if (count($profile->getChildOrderIds()) && current($profile->getChildOrderIds()) == "-1")
            return true;

        return false;
    }

    public function getServerToServerParameters($remoteIp)
    {
        $parameters = array();
        $parameters['CLIENTIP'] = $remoteIp;
        $parameters['CLIENTREFERRER'] = Mage::helper('core/http')->getRequestUri() != '' ? Mage::helper('core/http')->getRequestUri() : 'Unknow';
        $parameters['CLIENTUSERAGENT'] = Mage::helper('core/http')->getHttpUserAgent() != '' ? Mage::helper('core/http')->getHttpUserAgent() : 'Server';

        return $parameters;
    }

    protected function responseToPayment($payment, $response = null)
    {
        if (is_null($response))
            $response = $this->getResponse();

        $payment->setTransactionId($response->getTransactionId());
        $payment->setCcExpMonth($response->getCcExpMonth());
        $payment->setCcExpYear($response->getCcExpYear());
        $payment->setCcOwner($response->getCcOwner());
        $payment->setCcType($response->getCcType());
        $payment->setCcStatusDescription($response->getCcStatusDescription());
        $payment->setCcLast4($response->getCcLast4());
        $payment->setCcNumberEnc($response->getCcNumberEnc());

        return $this;
    }

    protected function responseToCustomer($customer, $response = null)
    {
        if (is_null($response))
            $response = $this->getResponse();

        $customer->setBe2billAlias($response->getAlias());
        $customer->setBe2billCcExpDate($response->getCcValidityDate());
        $customer->setBe2billCcNumberEnc($response->getCcNumberEnc());

        $customer->getResource()->saveAttribute($customer, 'be2bill_alias');
        $customer->getResource()->saveAttribute($customer, 'be2bill_cc_exp_date');
        $customer->getResource()->saveAttribute($customer, 'be2bill_cc_number_enc');

        return $this;
    }

    public function ipnPostSubmit()
    {
        if (!$this->hasResponse())
            Mage::throwException("NO Response in IPN");

        $this->_debug($this->getResponse()->setData("method", __METHOD__));

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getResponse()->getIncrementId());

        if (!$order->getId() && strpos($this->getResponse()->getIncrementId(), 'recurring') === false)
            Mage::throwException("NO Order Found!");

        if (strpos($this->getResponse()->getIncrementId(), 'recurring') !== false) {
            //return $this;
            list($action, $type, $profileId) = explode("-", $this->getResponse()->getIncrementId());

            if ($profileId) {
                /* @var $profile Mage_Sales_Model_Recurring_Profile */
                $profile = Mage::getModel('sales/recurring_profile')->load($profileId);
                if ($profile->getId()) {
                    if ($this->getResponse()->hasAlias() && trim($this->getResponse()->getAlias()) != "") {
                        $customer = Mage::getModel('customer/customer')->load($profile->getCustomerId());
                        if ($customer->getId()) {
                            $this->responseToCustomer($customer);
                            Mage::log("action = " . $action, null, "debug_recurring.log");
                            if ($action == 'create' || $action == "payment") {
                                $this->createProfileOrder($profile, $this->getResponse());
                            }
                            return $this;
                        }
                        Mage::throwException(Mage::helper('be2bill')->__("Customer %d not found (Recurring).", $profile->getCustomerId()));
                    }
                    Mage::throwException(Mage::helper('be2bill')->__("Alias not present (Recurring)."));
                }
                Mage::throwException(Mage::helper('be2bill')->__("Profile for ID: %d doesn't exists (Recurring).", $profileId));
            }
            Mage::throwException(Mage::helper('be2bill')->__("Order Id not present (Recurring)."));
        }

        $sendOrderEmail = false;
        $lastTxnId = $order->getPayment()->getLastTransId();

        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $order->getPayment();
        //need to save transaction id
        $this->responseToPayment($payment);

        if ($this->getResponse()->isSuccess() && $this->getResponse()->getTransactionId() != $lastTxnId) {
            if ($this->getResponse()->hasAlias() && trim($this->getResponse()->getAlias()) != "") {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if ($customer->getId()) {
                    $this->responseToCustomer($customer);
                }
            }

            switch ($this->getResponse()->getOperationType()) {
                case self::OPERATION_TYPE_PAYMENT: {
                        if ($payment->getMethod() == 'be2bill_several' && $order->hasInvoices()) {
                            $order->addStatusToHistory($order->getStatus(),
                                    // keep order status/state
                                    Mage::helper('be2bill')->__('New debit with code %s and message: %s.', $this->getResponse()->getExecCode(), $this->getResponse()->getMessage())
                            );

                            $order->save();
                        } else {
                            if ($order->isCanceled()) {
                                foreach ($order->getAllItems() as $item) {
                                    $item->setQtyCanceled(0);
                                }
                            }
                            $newOrderStatus = "processing";
                            //need to convert from order into invoice
                            $invoice = $order->prepareInvoice();
                            $invoice->register()->capture();
                            Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)->addObject($invoice->getOrder())
                                    ->save();

                            $order->setState(
                                    Mage_Sales_Model_Order::STATE_PROCESSING, $newOrderStatus, Mage::helper('be2bill')->__('Invoice #%s created', $invoice->getIncrementId()), $notified = true
                            );

                            //for compatibility
                            /* if (method_exists($payment, 'addTransaction')) {
                              $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $order);
                              } */
                            $sendOrderEmail = true;
                        }
                        break;
                    }
                case self::OPERATION_TYPE_AUTH: {
                        if ($order->isCanceled()) {
                            foreach ($order->getAllItems() as $item) {
                                $item->setQtyCanceled(0);
                            }
                        }
                        $payment->setIsTransactionClosed(0);
                        $payment->authorize(false, $order->getBaseTotalDue());

                        $newOrderStatus = 'pending_be2bill';
                        $order->setState(
                                Mage_Sales_Model_Order::STATE_PROCESSING, $newOrderStatus, Mage::helper('be2bill')->__("Waiting for capture transaction ID '%s'of amount %s", $this->getResponse()->getTransactionId(), $order->getBaseCurrency()->formatTxt($order->getBaseTotalDue())), $notified = true
                        );

                        //for compatibility
                        /* if (method_exists($payment, 'addTransaction')) {
                          $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, $order);
                          } */
                        $sendOrderEmail = true;
                        break;
                    }
                default: {
                        $order->addStatusToHistory($order->getStatus(),
                                // keep order status/state
                                Mage::helper('be2bill')->__('Notification from Be2bill. Operation type: "%s", transaction ID: %s, execcode: "%s", message: "%s".', $this->getResponse()->getOperationType(), $this->getResponse()->getTransactionId(), $this->getResponse()->getExecCode(), $this->getResponse()->getMessage())
                        );
                        break;
                    }
            }

            $order->save();
            if ($sendOrderEmail)
                $order->sendNewOrderEmail();
        } elseif ($this->getResponse()->getTransactionId() == "" || $this->getResponse()->getTransactionId() != $lastTxnId) {
            $order->addStatusToHistory($order->getStatus(),
                    // keep order status/state
                    Mage::helper('be2bill')->__('Error in processing payment with code %s and message: %s.', $this->getResponse()->getExecCode(), $this->getResponse()->getMessage())
            );
            $order->save();
        }

        return $this;
    }

    /**
     * Refund money
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_currentOperationType = self::OPERATION_TYPE_REFUND;

        parent::refund($payment, $amount);

        $params = $this->getApi()->getBaseParameters();
        $params['TRANSACTIONID'] = $payment->getParentTransactionId();
        $params['ORDERID'] = $payment->getOrder()->getIncrementId();
        $params['AMOUNT'] = $this->formatAmount($amount);
        $params['OPERATIONTYPE'] = $this->getOperationType();
        $params['HASH'] = $this->generateHASH($params);

        $service = $this->getApi();
        $this->_debug($params);
        $response = $service->send($this->getOperationType(), $params);
        $this->_debug($response);
        if (!$response->isSuccess()) {
            if ($response->getData('execcode') == '2008')
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('be2bill')->__('Order paid in 3 times. Please refund the order in the Be2bill Extranet'));
            else
                Mage::throwException("Error code: " . $response->getExeccode() . " " . $response->getMessage());

            Mage::logException(new Exception("Response: " . print_r($response->getData(), 1)));
        } else {
            $payment->setTransactionId($response->getTransactionId());
            $payment->setIsTransactionClosed(1);
        }

        return $this;
    }

    /**
     *
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @param Profileolabs_Be2bill_Model_Api_Response $response
     * @return Mage_Sales_Model_Order
     */
    protected function createProfileOrder(Mage_Sales_Model_Recurring_Profile $profile, Profileolabs_Be2bill_Model_Api_Response $response)
    {
        $amount = $this->getAmountFromProfile($profile);
        $productItemInfo = new Varien_Object;
        $type = "Regular";

        if ($type == 'Trial') {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
        } elseif ($type == 'Regular') {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
        }

        if ($this->isInitialProfileOrder($profile))// because is not additonned in prodile obj
            $productItemInfo->setPrice($profile->getBillingAmount() + $profile->getInitAmount());

        $order = $profile->createOrder($productItemInfo);

        $this->responseToPayment($order->getPayment(), $response);

        $order->save();

        $profile->addOrderRelation($order->getId());
        $order->getPayment()->registerCaptureNotification($amount);
        $order->save();

        // notify customer
        if ($invoice = $order->getPayment()->getCreatedInvoice()) {
            $message = Mage::helper('be2bill')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
            $comment = $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                    ->setIsCustomerNotified(true)
                    ->save();

            /* Add this to send invoice to customer */
            $invoice->setEmailSent(true);
            $invoice->save();
            $invoice->sendEmail();
        }

        return $order;
    }

    public function subscription(Mage_Sales_Model_Recurring_Profile $profile)
    {
        $this->_currentOperationType = self::OPERATION_TYPE_PAYMENT;
        $orderInfo = unserialize($profile->getOrderInfo());
        //$orderItemInfo = unserialize($profile->getOrderItemInfo());
        //$billingAddressInfo = unserialize($profile->getBillingAddressInfo());
        //$shippingAddressInfo = unserialize($profile->getShippingAddressInfo());

        $amount = $this->getAmountFromProfile($profile);
        if (!count($profile->getChildOrderIds()))
            $amount += $profile->getInitAmount();

        $params = array_merge($this->getApi()->getBaseParameters(), $this->getServerToServerParameters($orderInfo['remote_ip']));

        $params['CLIENTIDENT'] = $orderInfo['customer_id'];
        $params['CLIENTEMAIL'] = $orderInfo['customer_email'];
        $params['ORDERID'] = "payment-recurring-" . $profile->getId(); //"payment-recurring-".$orderInfo['entity_id'];;
        $params['AMOUNT'] = $this->formatAmount($amount);
        $params['DESCRIPTION'] = $params['DESCRIPTION'] . " (Recurring)";
        $params['OPERATIONTYPE'] = $this->getOperationType();

        $customer = Mage::getModel('customer/customer')->load($orderInfo['customer_id']);

        $params['ALIAS'] = $customer->getBe2billAlias();
        $params['ALIASMODE'] = self::OPERATION_TYPE_SUBSCRIPTION;
        $params['HASH'] = $this->generateHASH($params);

        $paramsTolog = $params;
        $service = $this->getApi();
        $debugData = array_merge($paramsTolog, array("method" => __METHOD__));
        $this->_debug($debugData);

        $response = $service->send($this->getOperationType(), $params);
        $this->setData('response', $response);
        $this->_debug($response);

        if (!$response->isSuccess()) {
            Mage::logException(new Exception("Response: " . print_r($response->getData(), 1)));
        }

        return $this;
    }

    /**
     * Send Oneclick
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function oneclick(Varien_Object $payment, $amount)
    {
        $params = array_merge($this->getApi()->getBaseParameters(), $this->getServerToServerParameters($payment->getOrder()->getRemoteIp()));

        $order = $payment->getOrder();
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();

        $params['CLIENTIDENT'] = $order->getCustomerId();
        $params['CLIENTEMAIL'] = $address->getEmail();
        $params['ORDERID'] = $order->getIncrementId();
        $params['AMOUNT'] = $this->formatAmount($amount);
        $params['DESCRIPTION'] = $params['DESCRIPTION'] . " (One click)";
        $params['OPERATIONTYPE'] = $this->getOperationType();

        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $params['ALIAS'] = $customer->getBe2billAlias();
        $params['ALIASMODE'] = self::OPERATION_TYPE_ONECLICK;

        if ($this->getConfigData('use_cvv_oneclick')) {
            $params['CARDCVV'] = $this->getCardCVV($payment);
        }

        if ($this->getCode() == 'be2bill_several') {
            $params['AMOUNTS'] = $this->getAmountByPeriod($params['AMOUNT']);
            unset($params['AMOUNT']);
        }

        $params['HASH'] = $this->generateHASH($params);

        $service = $this->getApi();
        $debugData = array_merge($params, array("method" => __METHOD__));
        $this->_debug($debugData);

        $response = $service->send($this->getOperationType(), $params);
        $this->setData('response', $response);
        $this->_debug($response);

        if (!$response->isSuccess()) {
            Mage::logException(new Exception("Response: " . print_r($response->getData(), 1)));
            Mage::throwException("Error code: " . $response->getExeccode() . " " . $response->getMessage());
        } else {
            $this->responseToPayment($payment, $response);
        }

        return $this;
    }

    /**
     * @return Quadra_Be2bill_Model_Api_Response
     */
    public function getResponse()
    {
        return $this->getData('response');
    }

    public function getOperationType()
    {
        if (empty($this->_currentOperationType)) {
            switch ($this->getConfigPaymentAction()) {
                case self::ACTION_AUTHORIZE:
                    $this->_currentOperationType = self::OPERATION_TYPE_AUTH;
                    break;
                case self::ACTION_AUTHORIZE_CAPTURE:
                    $this->_currentOperationType = self::OPERATION_TYPE_PAYMENT;
                    break;
            }
        }
        return $this->_currentOperationType;
    }

    public function getDescription()
    {
        return ucfirst(Mage::helper('be2bill')->__($this->getOperationType()));
    }

    /**
     *
     * @param Quadra_Be2bill_Model_Api_Response $response
     */
    public function setResponse($response)
    {
        $this->setData('response', $response);
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            Mage::getModel('be2bill/log_adapter', 'payment_' . $this->getCode() . '.log')
                    ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                    ->log($debugData);
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }

}
