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
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Zemode_Shell_StarnetUpdateSku extends Mage_Shell_Abstract
{
    
    const FILENAME = 'ps_mdb_product_maillot.csv';
    
    protected $_attributeOptions = array();
    
    /**
     * Get CSV stream
     * 
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        $io     = new Varien_Io_File();
        $info   = pathinfo(self::FILENAME);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // Skip headers
        $headers = $io->streamReadCsv();
        
        return $io;
    }
    
    /**
     * Get Connection object
     *
     * @return Object
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('catalog_read');
    }

    /**
     * Get Select object
     *
     * @return Object
     */
    protected function _getSelect()
    {
        return $this->_getConnection()->select();
    }
    
    /**
     * Get Table
     *
     * @param string $table
     * @return string
     */
    protected function _getTable($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }
    
    /**
     * Get Attribute Options
     *
     * @param string $attCode
     * @return array
     */
    protected function _getAttributeOptions($attCode)
    {
        if (! array_key_exists($attCode, $this->_attributeOptions)) {
            $select = $this->_getSelect()
                ->from(array('a'=>$this->_getTable('eav/attribute')))
                ->join(array('ao'=>$this->_getTable('eav/attribute_option')), 'ao.attribute_id=a.attribute_id', array('ao.option_id'))
                ->where('a.attribute_code = (?)', $attCode)
                ->joinLeft(
                    array('store'=>$this->_getTable('eav/attribute_option_value')),
                    "store.option_id=ao.option_id and store.store_id=0",
                    array('value'=>"store.value")
                );

            $rawOptions = $this->_getConnection()->fetchAll($select);
            $options = array();
            foreach ($rawOptions as $rawOption) {
                $options[$rawOption['value']] = $rawOption['option_id'];
            }
            $this->_attributeOptions[$attCode] = $options;
        }

        return $this->_attributeOptions[$attCode];
    }
    
    /**
     * Get Attribute Option Id By Value
     *
     * @param string $attCode
     * @param string $value
     * @return int
     */
    protected function _getAttributeOptionIdByValue($attCode, $value)
    {
        $options = $this->_getAttributeOptions($attCode);
        return $options[$value];
    }
    
    /**
     * Try to update Magen,to SKU with starnet csv file
     * 
     * @return null
     */
    public function run()
    {        
        $this->_updatedRows = 0;
        
        $io = $this->_getCsvStream();
        
        try {
            $rowNumber  = 1;

            while (false !== ($csvLine = $io->streamReadCsv(","))) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }
                
                // Empay SKU
                if (! isset($csvLine[0])) {
                    continue;
                }

                // Load product by SKU
                $prd = Mage::getModel('catalog/product');
                $prd->load($prd->getIdBySku($csvLine[0]));
                
                if (! $prd->getId()) {
                    Mage::log('SKU '.$csvLine[0].' non trouvée dans Magento');
                    continue;
                }
                
                $csvLine[3] = ucfirst($csvLine[3]);
                
                // Check type ID
                if ($prd->getData('type_id') == 'configurable') {
                    // If configurable we load all associated simples
                    $childIds = $prd->getTypeInstance()->getChildrenIds($prd->getId());
                    
                    // Parse all child
                    foreach ($childIds[0] as $childId) {
                        $prdChild = Mage::getModel('catalog/product')->load($childId);
                        $prdChild->setData('cost', $csvLine[2])
                                ->setData('supplier', $this->_getAttributeOptionIdByValue('supplier', $csvLine[3]))
                                //->setData('price', $csvLine[4])
                                ->save();
                        Mage::log('SKU '.$prdChild->getData('sku'). ' (simple) mis à jour (cost, supplier)');
                    }
                    
                }
                
                // Update data
                $prd->setData('ps_url', $csvLine[1])
                     ->setData('cost', $csvLine[2])
                     ->setData('supplier', $this->_getAttributeOptionIdByValue('supplier', $csvLine[3]))
                     ->setData('price', $csvLine[4])
                     ->save();
                Mage::log('SKU '.$csvLine[0]. ' (configurable) mis à jour (ps_url, cost, supplier)');

                $this->_updatedRows++;
            }
        } catch (Mage_Core_Exception $e) {
            $io->streamClose();
            echo 'Erreur : '.$e->getMessage();
        } catch (Exception $e) {
            $io->streamClose();
            Mage::logException($e);
            echo 'Erreur : '.$e->getMessage();
        }

        echo "\n\n Nb lignes mises à jour : ".$this->_updatedRows."\n\n";
        
        return $this;
    }
}

$shell = new Zemode_Shell_StarnetUpdateSku();
$shell->run();
