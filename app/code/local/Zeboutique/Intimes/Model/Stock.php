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
 * @package     Zeboutique_Intimes
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Intimes
 *
 * @category    Zeboutique
 * @package     Zeboutique_Intimes
 * @author      Zeboutique
 */
class Zeboutique_Intimes_Model_Stock extends Zeboutique_Zcore_Model_Stock
{
    
    const URL_STOCK_FILE = 'http://www.grossiste-intime.com/telechargement/stock.csv';
    
    protected $_prefix = 'intimes';

    /**
     * Get CSV stream
     * 
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        $io = new Varien_Io_File();
        if ($io->streamOpen(self::URL_STOCK_FILE, 'r') === false) {
            $this->_log('File does not exist');
            exit;
        }
        
        return $io;
    }
    
    /**
     * Try to update Magento SKU with intimes csv file
     * 
     * @return null
     */
    protected function _prepareData()
    {
        $io = $this->_getCsvStream();
        
        try {
            while (false !== ($csvLine = $io->streamReadCsv(","))) {
                if (count($csvLine) >= 2) {
                    $this->_stockData[] = array($csvLine[0], $csvLine[1]);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            echo 'Erreur : '.$e->getMessage();
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            echo 'Erreur : '.$e->getMessage();
        }
        
        return $this;
    }
}