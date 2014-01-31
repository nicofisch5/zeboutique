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
class Quadra_Be2bill_SeveralController extends Quadra_Be2bill_Controller_Action
{

    /**
     * Get singleton with be2bill several order transaction information
     *
     * @return Quadra_Be2bill_Model_Several
     */
    public function getMethodInstance()
    {
        return Mage::getSingleton('be2bill/several');
    }

}
