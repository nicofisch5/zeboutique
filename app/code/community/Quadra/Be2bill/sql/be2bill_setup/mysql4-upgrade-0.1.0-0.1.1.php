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
$installerCustomer = new Mage_Customer_Model_Entity_Setup('be2bill_setup');
/* @var $installerCustomer Mage_Customer_Model_Entity_Setup */
$installerCustomer->startSetup();

$entityId = $installerCustomer->getEntityTypeId('customer');
$attribute = $installerCustomer->getAttribute($entityId, 'be2bill_alias');

if (!$attribute) {
    $installerCustomer->addAttribute('customer', 'be2bill_alias', array(
        'type' => 'varchar',
        'label' => 'Alias be2bill',
        'visible' => true,
        'required' => false,
        'unique' => false,
        'sort_order' => 700,
        'default' => 0,
        'input' => 'text',
    ));

    $usedInForms = array(
        'adminhtml_customer',
    );

    $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'be2bill_alias');
    $attribute->setData('used_in_forms', $usedInForms);
    $attribute->setData('sort_order', 700);
    $attribute->save();
}

$attribute = $installerCustomer->getAttribute($entityId, 'be2bill_cc_number_enc');
if (!$attribute) {

    $installerCustomer->addAttribute('customer', 'be2bill_cc_number_enc', array(
        'type' => 'varchar',
        'label' => 'Card number encrypted be2bill',
        'visible' => true,
        'required' => false,
        'unique' => false,
        'sort_order' => 750,
        'default' => 0,
        'input' => 'text',
    ));

    $usedInForms = array(
        'adminhtml_customer',
    );

    $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'be2bill_cc_number_enc');
    $attribute->setData('used_in_forms', $usedInForms);
    $attribute->setData('sort_order', 700);
    $attribute->save();
}

$attribute = $installerCustomer->getAttribute($entityId, 'be2bill_cc_exp_date');
if (!$attribute) {
    $installerCustomer->addAttribute('customer', 'be2bill_cc_exp_date', array(
        'type' => 'varchar',
        'label' => 'Card expiration date be2bill',
        'visible' => true,
        'required' => false,
        'unique' => false,
        'sort_order' => 750,
        'default' => 0,
        'input' => 'text',
    ));

    $usedInForms = array(
        'adminhtml_customer',
    );

    $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'be2bill_cc_exp_date');
    $attribute->setData('used_in_forms', $usedInForms);
    $attribute->setData('sort_order', 700);
    $attribute->save();
}

$installerCustomer->endSetup();
