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
 * @package     Zeboutique_Chantefeuille
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Chantefeuille
 *
 * @category    Zeboutique
 * @package     Zeboutique_Chantefeuille
 * @author      Zeboutique
 */
class Zeboutique_Chantefeuille_Model_Stock extends Zeboutique_Zcore_Model_Stock
{
    
    //const URL_STOCK_FILE = 'http://www.chantefeuille.fr/modules/moussiq/export/7146bb50a9f93537b5e196e3f92b0fcb04428668.csv';
    const URL_STOCK_FILE = 'http://www.cfeminin.com/modules/moussiq/export/7146bb50a9f93537b5e196e3f92b0fcb04428668.csv';
    
    protected $_prefix = 'chantefeuille';

    /**
     * Get CSV stream
     * 
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        $io = new Varien_Io_File();
        $io->streamOpen(self::URL_STOCK_FILE, 'r');

        // Skip headers
        $headers = $io->streamReadCsv();
        
        return $io;
    }
    
    /**
     * Try to update Magen,to SKU with chantefeuille csv file
     * 
     * @return null
     */
    protected function _prepareData()
    {
        $io = $this->_getCsvStream();
        
        try {
            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                // Before injecting data we check if SKU already exists in file
                if (array_key_exists($csvLine[1], $this->_stockData)) {
                    // If exists we check label
                    if (substr($csvLine[3], 0, 7) == 'Maillot') {
                        continue;
                    }
                }

                $this->_stockData[$csvLine[1]] = array($csvLine[1], $csvLine[7]);
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