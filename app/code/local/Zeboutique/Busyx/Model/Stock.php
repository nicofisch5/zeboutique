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
 * @package     Zeboutique_Busyx
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Busyx
 *
 * @category    Zeboutique
 * @package     Zeboutique_Busyx
 * @author      Zeboutique
 */
class Zeboutique_Busyx_Model_Stock extends Zeboutique_Zcore_Model_Stock
{
    
    const URL_STOCK_FILE = 'http://www.busyx-pro.com/csv_proengine.php';
    const PATH_STOCK_FILE = 'var/import/';
    const BUSYX_OPTION_ID = 314;

    protected $_prefix = 'busyx';

    /**
     * Get CSV stream
     * 
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        try {
            // Ourput file
            $outputfile = self::PATH_STOCK_FILE.$this->_prefix.".csv";
            // Prepare shell command
            $cmd = "wget -q \"".self::URL_STOCK_FILE."\" -O $outputfile";
echo $cmd;
            // Execute shell command
            exec($cmd);

            $io = new Varien_Io_File();
            if ($io->streamOpen($outputfile, 'r') === false) {
                $this->_log('File does not exist');
                exit;
            }

        } catch (Exception $e) {
            $this->_log($e->getMessage(), Zend_Log::ERR);
            exit;
        }

        // Skip headers
        if ($io->streamReadCsv() === false) {
            $this->_log('Empty file');
            exit;
        }
        
        return $io;
    }
    
    /**
     * Try to update Magento SKU with Busyx csv file
     * 
     * @return null
     */
    protected function _prepareData()
    {
        $io = $this->_getCsvStream();
        
        try {
            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $this->_stockData[] = array($csvLine[1], 1);
            }
        } catch (Mage_Core_Exception $e) {
            //$adapter->rollback();
            $io->streamClose();
            echo 'Erreur : '.$e->getMessage();
        } catch (Exception $e) {
            //$adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            echo 'Erreur : '.$e->getMessage();
        }
        
        return $this;
    }

    /**
     * Before reindex stock data
     * Update all lines that are not in Busyx stock file
     *
     * @return Zeboutique_Zcore_Model_Stock
     */
    protected function _beforeReindexStock()
    {
        // Get all Busyx products that are not in Busyx file
        $coll = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('type_id', 'simple')
            ->addFieldToFilter('supplier', self::BUSYX_OPTION_ID)
            ->addFieldToFilter('entity_id', array('nin' => $this->_prdIdsInFile));
        $this->_log($coll->getSelect());

        // Update with stock 0
        $this->_setStockData($coll->getAllIds(), 0);

        return $this;
    }
}
