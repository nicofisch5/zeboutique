<?php
/*------------------------------------------------------------------------
# APL Solutions and Vision Co., LTD
# ------------------------------------------------------------------------
# Copyright (C) 2008-2010 APL Solutions and Vision Co., LTD. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: APL Solutions and Vision Co., LTD
# Websites: http://www.joomlavision.com/ - http://www.magentheme.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Verticalmenu_Model_Config_Style
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value'=>'accordion', 'label'=>Mage::helper('adminhtml')->__('Yes, Accordion')),
            array('value'=>'dropdown', 'label'=>Mage::helper('adminhtml')->__('Yes, Dropdown')),
            array('value'=>'tree', 'label'=>Mage::helper('adminhtml')->__('Yes, Tree')),
        );
    }

}
