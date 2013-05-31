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
class Zemode_Shell_BusyxUpdateSku extends Mage_Shell_Abstract
{
    
    const FILENAME = 'busyx_pro.csv';
    const BUSYX_OPTION_ID = 314;

    // Default
    protected $_attSet = 4;

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
     * Try to update Magento SKU with Busyx csv file
     * 
     * @return null
     */
    public function run()
    {
        $this->_updatedRows = 0;
        
        $io = $this->_getCsvStream();
        
        try {
            $rowNumber  = 1;
            $importData = array();

            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                /**
                 * 1. Load le produit simple dont le SKU est égal à la référence maître
                 * 2. Pour ce produit on essaye de trouver l'intitulé de la couleur Magento dans l'intitulé starnet
				 *	- si on trouve on met à jour la référence.
                 *  - sinon on loggue
                */

                $coll = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('type_id', 'simple')
                    ->addFieldToFilter('sku', $csvLine[0])
                    ->addFieldToFilter('attribute_set_id', $this->_attSet)
                    ->addFieldToFilter('visibility', 4);

                if ($coll->getSize() != 1) {
                    continue;
                }

                $prd = $coll->getLastItem();
                $prd->load($prd->getId());
                if (! $prd->getId()) {
                    continue;
                }
                    
                    // Update SKU
                    $this->_updatedRows++;
                    $msg = $prd->getSku()." - New SKU : ".$csvLine[1]." (".$prd->getId().") \n";

                    echo $msg;
                    Mage::log($msg);
                    $prd->setData('sku', $csvLine[1])
                        ->setData('starnet_sku_updated', 1)
                        ->setData('supplier', self::BUSYX_OPTION_ID)
                        ->save();

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

        echo "\n\n Nb lignes mises à jour : ".$this->_updatedRows."\n\n";
        
        return $this;
    }
}

$shell = new Zemode_Shell_BusyxUpdateSku();
$shell->run();
