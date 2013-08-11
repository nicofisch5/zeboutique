<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Block_Adminhtml_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        // add required javascript
        $this->getLayout()->getBlock('head')->addJs('amasty/amcatcopy/duplicate.js');
        
        // add duplicate button
        if ($this->getCategory()->getId())
        {
            $this->addAdditionalButton('duplicate_button', array(
                        'label'     => Mage::helper('amcatcopy')->__('Duplicate Category'),
                        'onclick'   => "categoryDuplicate('".$this->getUrl('amcatcopy/adminhtml_index/duplicate', array('id' => $this->getCategory()->getId()))."')",
                        'class'     => 'add'
                    ));
        }
        
        return $this;
    }
    
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if (Mage::app()->getRequest()->getParam('duplicated'))
        {
            $html .= '<script type="text/javascript">
                         amCatCopy_Duplicated_Id = ' . intval(Mage::app()->getRequest()->getParam('duplicated')) . ';
                         amCatCopy_updateContent_Url = "' . $this->getUrl('*/*/edit', array('id' => intval(Mage::app()->getRequest()->getParam('duplicated')))) . '";
                      </script>';
        }
        return $html;
    }
}