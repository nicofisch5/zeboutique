<?php

class Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Renderer_Stock extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
		$value = $row->getData($this->getColumn()->getIndex());
		if($value==1) return Mage::helper('comparer')->__('In stock');
		else return Mage::helper('comparer')->__('Out of stock');
    }
}