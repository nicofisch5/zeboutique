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
class Quadra_Be2bill_Model_Api_Response extends Varien_Object
{

    protected $_codeToMessages = array();

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return Varien_Object
     */
    public function setData($key, $value = null)
    {
        $data = $key;
        if (is_array($key)) {
            $data = array();
            foreach ($key as $oldKey => $value) {
                $newKey = strtolower($oldKey);
                $data[$newKey] = $value;
            }
        }

        return parent::setData($data, $value);
    }

    public function isSuccess()
    {
        return $this->getExecCode() == "0000";
    }

    public function getExecCode()
    {
        return $this->getData('execcode');
    }

    public function getCodeToMessage($execode)
    {
        if (!count($this->_codeToMessages)) {
            $this->_codeToMessages = array(
                4001 => Mage::helper('be2bill')->__('The bank refused the transaction.'),
                4002 => Mage::helper('be2bill')->__('Insufficient funds.'),
                4003 => Mage::helper('be2bill')->__('Card refused by the bank networks.'),
                4004 => Mage::helper('be2bill')->__('The transaction has been abandoned.'),
                4005 => Mage::helper('be2bill')->__('Fraud suspicion.'),
                4006 => Mage::helper('be2bill')->__('The card has been declared as lost.'),
                4007 => Mage::helper('be2bill')->__('The card has been declared as stolen.'),
                4008 => Mage::helper('be2bill')->__('The 3D secure authentication failed.'),
                4009 => Mage::helper('be2bill')->__('The 3D secure authentication request has expired.'),
                4010 => Mage::helper('be2bill')->__('Invalid transaction.'),
                4011 => Mage::helper('be2bill')->__('Duplicate request.'),
                4012 => Mage::helper('be2bill')->__('Invalid card data.'),
                4013 => Mage::helper('be2bill')->__('Transaction not allowed by bank networks to the card holder.'),
                5001 => Mage::helper('be2bill')->__('Exchange protocol failure.'),
                5002 => Mage::helper('be2bill')->__('Bank networks failure.'),
                5003 => Mage::helper('be2bill')->__('System under maintenance, please try again later.'),
                5004 => Mage::helper('be2bill')->__('Timeout. The result will be sent to notification url.'),
                5005 => Mage::helper('be2bill')->__('3D secure authentification error.'),
                6001 => Mage::helper('be2bill')->__('Transaction declined by merchant.'),
                6002 => Mage::helper('be2bill')->__('Transaction declined.'),
                6003 => Mage::helper('be2bill')->__('Cardholder has already disputed a transaction.'),
                6004 => Mage::helper('be2bill')->__('Transaction declined by merchant\'s rules.')
            );
        }

        $message = trim(Mage::getStoreConfig('be2bill/be2bill_errors/error_' . $execode));
        if (strlen($message))
            return $message;
        elseif (isset($this->_codeToMessages[$execode]))
            return $this->_codeToMessages[$execode];
        else
            return false;
    }

    public function getMessage()
    {
        $execode = (int) $this->getExecCode();
        if (!$message = $this->getCodeToMessage($execode))
            $message = $this->getData('message');

        return Mage::helper('be2bill')->__($message);
    }

    public function getIncrementId()
    {
        return $this->getData('orderid');
    }

    public function getTransactionId()
    {
        return $this->getData('transactionid');
    }

    public function getCcLast4()
    {
        return substr($this->getData('cardcode'), strlen($this->getData('cardcode')) - 4);
    }

    public function getCcType()
    {
        return $this->getData('cardtype');
    }

    public function getCcValidityDate()
    {
        return $this->getData('cardvaliditydate');
    }

    public function getCcExpMonth()
    {
        if ($this->getCcValidityDate() == "")
            return "";

        list($ccExpMonth, $ccExpYear) = explode("-", $this->getCcValidityDate());
        return $ccExpMonth;
    }

    public function getCcExpYear()
    {
        if ($this->getCcValidityDate() == "")
            return "";

        list($ccExpMonth, $ccExpYear) = explode("-", $this->getCcValidityDate());
        return $ccExpYear;
    }

    public function getCcOwner()
    {
        return $this->getData('cardfullname');
    }

    public function getCcStatusDescription()
    {
        return $this->getData('descriptor');
    }

    public function getCcNumberEnc()
    {
        return $this->getData('cardcode');
    }

    public function getOperationType()
    {
        return $this->getData('operationtype');
    }

    public function getAlias()
    {
        return $this->getData('alias');
    }

    public function getExtradata()
    {
        return $this->getData('extradata');
    }

}
