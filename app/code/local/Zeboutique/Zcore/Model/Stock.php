<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Zeboutique
 * @package     Zeboutique_Core
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Starnet
 *
 * @category    Zeboutique
 * @package     Zeboutique_Core
 * @author      Zeboutique
 */
abstract class Zeboutique_Zcore_Model_Stock extends Mage_Core_Model_Abstract
{
    
    protected $_prefix = '';
    protected $_prdIdsToReindex = array();
    protected $_stockData = array();
        
    protected function _getRawData() {}
    protected function _prepareData() {}
    
    /**
     * Set stock data
     *
     * @param int $prdId
     * @param int $qty
     * @return bool
     */
    protected function _setStockData($prdId, $qty)
    {
        // Table name
        $stockTable = $this->_getResource()->getTableName('cataloginventory/stock_item');
        
        $isInStock = 0;
        if ($qty > 0) {
            $isInStock = 1;
        }

        // Query
        $query = "UPDATE $stockTable
    	SET manage_stock = 1,
    	use_config_manage_stock = 0,
    	is_in_stock = ".$isInStock.",
    	qty = ".$qty."
        WHERE product_id = ".$prdId."
        AND stock_id = 1";
        
        $this->_getWriteConnection()->query($query);

        return true;
    }
    
    /**
     * Get stock data
     *
     * @param int $prdId
     * @return bool
     */
    protected function _getStockData($prdId)
    {
        // Table name
        $stockTable = $this->_getResource()->getTableName('cataloginventory/stock_item');

        // Query
        $query = "SELECT qty FROM $stockTable
        WHERE product_id = ".$prdId."
        AND stock_id = 1";
        
        $res = $this->_getReadConnection()->query($query)
                        ->fetch();

        return $res['qty'];
    }
    
    /**
     * Disable stock managing system
     *
     * @param int $stockId
     * @param int $prdId
     * @return Toolvelo_CatalogInventory_Helper_Product
     */
    protected function _disableManageStock($stockId, $prdId)
    {
        return $this->_modifyManageStock($stockId, $prdId, 0);
    }
    
    /**
     * Enable stock managing system
     *
     * @param int $stockId
     * @param int $prdId
     * @return Toolvelo_CatalogInventory_Helper_Product
     */
    protected function _enableManageStock($stockId, $prdId)
    {
        return $this->_modifyManageStock($stockId, $prdId, 1);
    }
    
    /**
     * Modify stock managing system
     *
     * @param int $stockId
     * @param int $prdId
     * @param int $manageStock
     * @return Toolvelo_CatalogInventory_Helper_Product
     */
    protected function _modifyManageStock($stockId, $prdId, $manageStock)
    {
        // Table name
        $stockTable = $this->_getResource()->getTableName('cataloginventory/stock_item');
        
        // Query
        $this->_getWriteConnection()->query("
            UPDATE $stockTable
        	SET manage_stock = ".$manageStock.", use_config_manage_stock = 0
            WHERE product_id = ".$prdId."
            AND stock_id = ".$stockId
        );
        
        return $this;
    }
    
    /**
     * Update stock in BDD
     *
     * @return Zeboutique_Zcore_Model_Stock
     */
    protected function _processToUpdateStock()
    {
        $productInstance = Mage::getModel('catalog/product');
        foreach ($this->_stockData as $line) {
            $sku = $line[0];
            $qty = $line[1];
            
            $productId = $productInstance->getIdBySku($sku);
            
            if (! $productId) {
                /*$this->_log(
                    Mage::helper('core')->__($this->_pathPrefix.' - SKU %s does not exist', $sku),
                    Zend_Log::ERR
                );*/
                
                continue;
            }
            
            // Check qty in stock
            if ((int) $this->_getStockData($productId) == $qty) {
                continue;
            }
        
            $this->_setStockData($productId, $qty);
            $this->_log(
                Mage::helper('core')->__('SKU %s has been updated with quantity %s', $sku, $qty)
            );
            
            $this->_prdIdsToReindex[] = $productId;
        }

        return $this;
    }
    
    /**
     * Reindex stock data
     *
     * @return Zeboutique_Zcore_Model_Stock
     */
    protected function _reindexStock()
    {
        Mage::getResourceSingleton('cataloginventory/indexer_stock')
                ->reindexProducts($this->_prdIdsToReindex);

        return $this;
    }

    /**
     * Update stock
     * 
     * @return Zeboutique_Zcore_Model_Stock
     */
    public function updateStock()
    {
        // Get Stock data from FTP
        $this->_getRawData();

        // Prepare data
        $this->_prepareData();

        $this->_log(
            Mage::helper('core')->__('Start update stock at %s', new Zend_Date())
        );
        // Update stock
        $this->_processToUpdateStock();
        $this->_log(
            Mage::helper('core')->__('End update stock at %s', new Zend_Date())
        );

        $this->_log(
            Mage::helper('core')->__('Start reindex stock at %s', new Zend_Date())
        );
        // Stock reindex
        $this->_reindexStock();
        $this->_log(
            Mage::helper('core')->__('End reindex stock at %s', new Zend_Date())
        );
        
        return $this;
    }
    
    /**
     * Log info
     * 
     * @param string $msg
     * @param int $level
     * @return Zeboutique_Zcore_Model_Stock
     */
    protected function _log($msg, $level = Zend_Log::INFO)
    {
        Mage::log(
                $this->_prefix.' - '. $msg,
                $level
        );
       
        return $this;
    }
    
    /**
     * Get read connection
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getReadConnection()
    {
        return $this->_getResource()->getConnection('core_read');
    }

    /**
     * Get write connection
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getWriteConnection()
    {
        return $this->_getResource()->getConnection('core_write');
    }
    
    /**
     * Get resource
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getResource()
    {
        return Mage::getSingleton('core/resource');
    }
    
}