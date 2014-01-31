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
class Quadra_Be2bill_Block_Method_Redirect extends Mage_Core_Block_Abstract
{

    protected $methodName = '';

    protected function _toHtml()
    {
        $method = Mage::getSingleton("be2bill/" . $this->getMethodName());

        $form = new Varien_Data_Form();
        $form->setAction($method->getRedirectUrl())
                ->setId('be2bill_' . $this->getMethodName() . '_checkout')
                ->setName('be2bill_' . $this->getMethodName() . '_checkout')->setMethod('POST')
                ->setUseContainer(true);

        foreach ($method->getCheckoutFormFields() as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $form->addField($field . "[" . $key . "]", 'hidden', array('name' => $field . "[" . $key . "]", 'value' => $val));
                }
            } else {
                $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
            }
        }

        $html = '<html><body>';
        $html .= $this->__('You will be redirected to Be2bill in a few seconds.');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">document.getElementById("be2bill_' . $this->getMethodName() . '_checkout").submit();</script>';
        $html .= '</body></html>';

        return $html;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

}
