<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Model_Catalog_Resource_Eav_Mysql4_Category extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category
{
	/**
	 * copied here from native magento code, as this function is partialy removed on 1.6
	 */
	public function move($categoryId, $newParentId)
    {
        $newParent = Mage::getModel('catalog/category')->load($newParentId);
        
        // update parent id
        $this->_getWriteAdapter()->query("UPDATE
            {$this->getEntityTable()} SET parent_id = {$newParent->getId()}
            WHERE entity_id = {$categoryId}");        
    }
    
    
    /**
     * Retrieve category tree object
     *
     * @return Varien_Data_Tree_Db
     */
    protected function _getTree()
    {
        /**
        * If we duplicating categories, should reload tree with every method call.
        * Seems to be an error when flat categories enabled.
        */
        if ('amcatcopy' == Mage::app()->getRequest()->getModuleName())
        {
            $this->_tree = Mage::getResourceModel('catalog/category_tree')
                    ->load();
        } else 
        {
            if (!$this->_tree) {
                $this->_tree = Mage::getResourceModel('catalog/category_tree')
                    ->load();
            }
        }
        return $this->_tree;
    }
}