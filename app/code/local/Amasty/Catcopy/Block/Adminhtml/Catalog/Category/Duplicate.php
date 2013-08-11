<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Block_Adminhtml_Catalog_Category_Duplicate extends Mage_Adminhtml_Block_Widget
{
    /**
    * Category ID to duplicate
    * 
    * @var integer
    */
    protected $_categoryId;
    
    /**
    * Define template
    * 
    */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amcatcopy/catalog/category/duplicate.phtml');
    }
    
    /**
    * Preparing block layout
    * 
    * @return Amasty_Catcopy_Block_Adminhtml_Catalog_Category_Duplicate
    */
    protected function _prepareLayout()
    {
        // add required javascript
        $this->getLayout()->getBlock('head')->addJs('amasty/amcatcopy/duplicate.js');
        $this->getLayout()->getBlock('head')->addJs('extjs/ext-tree.js');
        $this->getLayout()->getBlock('head')->addJs('extjs/ext-tree-checkbox.js');
        $this->getLayout()->getBlock('head')->addItem('js_css', 'extjs/resources/css/ext-all.css');
        $this->getLayout()->getBlock('head')->addItem('js_css', 'extjs/resources/css/ytheme-magento.css');
        
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('amcatcopy')->__('Duplicate'),
                    'onclick'   => 'processCategoryDuplicate();',
                    'class'     => 'save'
                ))
        );
        
        $this->setChild('parent_category_select', $this->getLayout()->createBlock('amcatcopy/adminhtml_catalog_category_duplicate_categories'));
        
        return parent::_prepareLayout();
    }
    
    /**
    * Retrieve Delete Button HTML
    * 
    * @return string
    */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }
    
    /**
    * Retrieve Parent Category Select HTML
    * 
    * @return string
    */
    public function getParentCategorySelectHtml()
    {
        return $this->getChildHtml('parent_category_select');
    }
    
    /**
    * Return save duplicated category url for form submit
    * 
    * @return string
    */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('id' => $this->getCategoryId()));
    }
    
    /**
    * Return header text for page
    * 
    * @return string
    */
    public function getHeaderText()
    {
        return  Mage::helper('amcatcopy')->__('Duplicate Category');
    }
    
    /**
    * Return category id to duplicate
    * 
    * @return integer
    */
    public function getCategoryId()
    {
        if (!$this->_categoryId)
        {
            $this->_categoryId = Mage::app()->getRequest()->getParam('id');
        }
        return $this->_categoryId;
    }
}