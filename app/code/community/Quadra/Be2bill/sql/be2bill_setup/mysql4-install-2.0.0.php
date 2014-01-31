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
$installer = $this;

$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');
$statusLabelTable = $installer->getTable('sales/order_status_label');

$installer->startSetup();

$currentVersion = Mage::getVersion();
if (version_compare($currentVersion, '1.4.2') == 1) {
    $status = 'pending_be2bill';
    $label = 'Pending Capture Be2bill';
    $code = "pending_payment";

    //Insert new Status in DB
    $data[0] = array(
        'status' => $status,
        'label' => $label,
    );

    $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
    //Insert relation between state and status
    $data[0] = array(
        'status' => $status,
        'state' => $code,
        'is_default' => 0,
    );

    $installer->getConnection()->insertArray(
        $statusStateTable, array('status', 'state', 'is_default'), $data
    );
}

$installer->endSetup();

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

$eavInstaller = new Mage_Eav_Model_Entity_Setup('be2bill_setup');

$eavInstaller->startSetup();

$entityTypeId = $eavInstaller->getEntityTypeId('catalog_product');

/**
 * Add 'be2bill_enable_oc_sev_payment' attribute to the 'eav/attribute' table
 */
$attribute = $eavInstaller->getAttribute($entityTypeId, 'be2bill_enable_oc_sev_payment');

if (!$attribute) {
    $eavInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'be2bill_enable_oc_sev_payment', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Is Product Available for Oneclick Several Payment with Be2bill',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => '',
        'is_configurable'   => false
    ));
}

$attributeSets = $eavInstaller->getConnection()->fetchAll('select attribute_set_id from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
$attributeId = $eavInstaller->getAttributeId('catalog_product', 'be2bill_enable_oc_sev_payment');

foreach ($attributeSets as $attributeSet) {
    /*
     * Create new attribute group
     */
    $setId = $attributeSet['attribute_set_id'];
    $eavInstaller->addAttributeGroup($entityTypeId, $setId, 'Be2bill Payment Options');

    /*
     * Add attribute to Be2bill Payment Options attribute group
     */
    $groupId = $eavInstaller->getAttributeGroupId($entityTypeId, $setId, 'Be2bill Payment Options');
    $eavInstaller->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId, 1);
}

/**
 * Add 'be2bill_3dsecure' attribute to the 'eav/attribute' table
 */
$attribute = $eavInstaller->getAttribute($entityTypeId, 'be2bill_3dsecure');

if (!$attribute) {
    $eavInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'be2bill_3dsecure', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Is Product Available for 3D Secure Payment',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => '',
        'is_configurable'   => false
    ));
}

$attributeSets = $eavInstaller->getConnection()->fetchAll('select attribute_set_id from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
$attributeId = $eavInstaller->getAttributeId('catalog_product', 'be2bill_3dsecure');

foreach ($attributeSets as $attributeSet) {
    /*
     * Add attribute to Be2bill Payment Options attribute group
     */
    $setId = $attributeSet['attribute_set_id'];
    $groupId = $eavInstaller->getAttributeGroupId($entityTypeId, $setId, 'Be2bill Payment Options');
    $eavInstaller->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId, 2);
}

$entityTypeId = $eavInstaller->getEntityTypeId('catalog_category');
$attribute = $eavInstaller->getAttribute($entityTypeId, 'be2bill_3dsecure');

if (!$attribute) {
    $eavInstaller->addAttribute(
        Mage_Catalog_Model_Category::ENTITY, 'be2bill_3dsecure', array(
            'type'              => 'int',
            'backend'           => '',
            'frontend'          => '',
            'label'             => 'Are Category\'s Products Available for 3D Secure Payment',
            'input'             => 'select',
            'class'             => '',
            'source'            => 'eav/entity_attribute_source_boolean',
            'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible'           => true,
            'required'          => false,
            'user_defined'      => true,
            'default'           => '0',
            'searchable'        => false,
            'filterable'        => false,
            'comparable'        => false,
            'visible_on_front'  => false,
            'unique'            => false,
            'apply_to'          => '',
            'is_configurable'   => false,
            'group'             => 'Be2bill Options'
    ));
}

$eavInstaller->endSetup();

