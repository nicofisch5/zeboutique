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
 * @package     Zeboutique_Starnet
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Starnet
 *
 * @category    Zeboutique
 * @package     Zeboutique_Starnet
 * @author      Zeboutique
 */
class Zeboutique_Starnet_Model_Stock extends Zeboutique_Zcore_Model_Stock
{
    
    const URL_STOCK_FILE = 'http://www.starnet-world.com/stock_starnet.csv';
    const URL_TARIF_FILE = 'http://www.starnet-world.com/files/tarifs_clients_2013.xlsx';
    const STARNET_OPTION_ID = 311;
    
    protected $_prefix = 'starnet';

    /**
     * Get CSV stream
     * 
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        $io     = new Varien_Io_File();
        //$info   = pathinfo(self::FILENAME);
        //$io->open(array('path' => $info['dirname']));
        $io->streamOpen(self::URL_STOCK_FILE, 'r');

        // Skip headers
        $headers = $io->streamReadCsv();
        
        return $io;
    }
    
    /**
     * Try to update Magen,to SKU with starnet csv file
     * 
     * @return null
     */
    protected function _prepareData()
    {
        $io = $this->_getCsvStream();
        
        try {
            $rowNumber  = 1;
            $importData = array();

            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $this->_stockData[] = array($csvLine[1], $csvLine[3]);
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