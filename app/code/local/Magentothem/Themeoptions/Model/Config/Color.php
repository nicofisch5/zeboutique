<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Themeoptions_Model_Config_Color
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'asparagus', 'label'=>Mage::helper('adminhtml')->__('asparagus')),
            array('value'=>'blue', 'label'=>Mage::helper('adminhtml')->__('blue')),
            array('value'=>'brick', 'label'=>Mage::helper('adminhtml')->__('brick')),
            array('value'=>'cardinal', 'label'=>Mage::helper('adminhtml')->__('cardinal')),
            
            array('value'=>'darkcoral', 'label'=>Mage::helper('adminhtml')->__('darkcoral')),
            array('value'=>'herbal', 'label'=>Mage::helper('adminhtml')->__('herbal')),
            array('value'=>'indigo', 'label'=>Mage::helper('adminhtml')->__('indigo')),
            array('value'=>'khaki', 'label'=>Mage::helper('adminhtml')->__('khaki')),
            
            array('value'=>'magentadye', 'label'=>Mage::helper('adminhtml')->__('magentadye')),
            array('value'=>'marengo', 'label'=>Mage::helper('adminhtml')->__('marengo')),
            array('value'=>'pumpkin', 'label'=>Mage::helper('adminhtml')->__('pumpkin')),
            array('value'=>'pear', 'label'=>Mage::helper('adminhtml')->__('pear')),
            
            array('value'=>'prussianblue', 'label'=>Mage::helper('adminhtml')->__('prussianblue')),
            array('value'=>'red', 'label'=>Mage::helper('adminhtml')->__('red')),
            array('value'=>'violetred', 'label'=>Mage::helper('adminhtml')->__('violetred')),
            array('value'=>'yellow', 'label'=>Mage::helper('adminhtml')->__('yellow'))
        );
    }

}
