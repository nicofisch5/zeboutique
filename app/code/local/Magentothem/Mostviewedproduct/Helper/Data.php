<?php

class Magentothem_Mostviewedproduct_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /**
     * Get store category filter
     * 
     * @param int $rootcatId
     * @return string
     */
    public function getStoreCategoryFilter($rootcatId)
    {
        $catColl = Mage::getModel('catalog/category')->getCollection()
                        ->addFieldToFilter('path', array('like'=>'%/'.$rootcatId.'/%'))
                        ->addAttributeToFilter('is_active', 1)
                        ->addStoreFilter();

        if ($catColl->getSize()) {
            return implode(',', $catColl->getAllIds());
        } else {
            return '';
        }
    }
}