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
class Quadra_Be2bill_Model_Several extends Quadra_Be2bill_Model_Standard
{

    protected $_code = 'be2bill_several';
    protected $_canAuthorize = false;

    public function getCheckoutFormFields()
    {
        $params = $this->getBaseParameters();
        $params['CREATEALIAS'] = 'yes'; //Forced
        $params['AMOUNTS'] = $this->getAmountByPeriod($params['AMOUNT']);

        unset($params['AMOUNT']);

        $params['OPERATIONTYPE'] = $this->getOperationType();
        $params['HASH'] = $this->generateHASH($params);

        $this->_debug($params);

        return $params;
    }

    public function getDescription()
    {
        return '3x ' . ucfirst(Mage::helper('be2bill')->__($this->getOperationType()));
    }

    protected function divideAmount($amount)
    {
        $intAmount = floor($amount / $this->getNTimes());
        $remainder = fmod($amount, $this->getNTimes());

        return array("intAmount" => $intAmount, 'remainder' => $remainder);
    }

    public function getAmountByPeriod($amount)
    {
        $amount = $this->unFormatAmount($amount);

        $divided = $this->divideAmount($amount);
        $periods = $this->getPeriods();

        $cntPeriods = count($periods);
        $i = 1;
        foreach ($periods as $key => $value) {
            if ($i == $cntPeriods)
                $periods[$key] = $this->formatAmount($divided['intAmount'] + $divided['remainder']);
            else
                $periods[$key] = $this->formatAmount($divided['intAmount']);
            $i++;
        }

        return $periods;
    }

    public function isAvailable($quote = null)
    {
        $parentActive = (bool) (int) Mage::getStoreConfig('payment/be2bill_standard/active', $quote ? $quote->getStoreId() : null);
        return parent::isAvailable($quote) && $parentActive;
    }

    protected function getPeriods()
    {
        $periods = array();
        $date = new DateTime();
        //$dateInterval = new DateInterval('P30D');
        $periods[$this->formatDate($date)] = 0;

        for ($i = 1; $i < $this->getNTimes(); $i++) {
            //$date->add($dateInterval);
            $date->modify("+30 day");
            $periods[$this->formatDate($date)] = 0;
        }

        return $periods;
    }

    protected function formatDate(DateTime $date, $format = 'Y-m-d')
    {
        return $date->format($format);
    }

    public function getNTimes()
    {
        return $this->getConfigData('n_times');
    }

}
