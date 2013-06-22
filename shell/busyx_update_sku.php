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
    const STARNET_OPTION_ID = 314;

    // C2c_Couleur_eros
    protected $_attSet = 20;

    // C2c_Couleur_Taille_pap
    //protected $_attSet = 10; // OK

    // Default
    // protected $_attSet = 4;

    protected $_taille = array(
        'x small' => 'XS-34',
        'small' => 'S-36',
        'large' => 'L-40',
        'medium' => 'M-38',
        'x large' => 'XL-42',
        '2x large' => 'XXL-44',
        '3x large' => 'XXXL-46',

        'lxl' => 'L/XL',
        'os' => 'Taille Unique',
        'ml' => 'M/L',
        'sm' => 'S/M',
    );
  
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
            $importData = array();

            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                /**
                 * 1. Load tout les produits simples dont le SKU commence par la référence maître
                 * 2. Pour chacun des produits de la collection on essaye de trouver l'intitulé de
                 * la couleur Magento dans l'intitulé starnet
				 *	- si on trouve on met à jour la référence.
                 *   - sinon on loggue
                */
                
                // Products that begin with starnet SKU master
                $coll = Mage::getModel('catalog/product')->getCollection()
                            ->addFieldToFilter('type_id', 'simple')
                            ->addFieldToFilter('sku', array('like'=>$csvLine[0].'%'))
                            ->addFieldToFilter('attribute_set_id', $this->_attSet)
                            ->addFieldToFilter('supplier', self::STARNET_OPTION_ID);

                // Skip line if no product in Magento
                if ($coll->getSize() == 0) {
                    //echo "Ref non trouvée ".$csvLine[0]." \n";
                    continue;
                }

                // Parse products collection
                foreach ($coll as $prd) {
                    $prd->load($prd->getId());

                    $couleur = strtolower($csvLine[10]);
                    //$taille = strtolower($csvLine[9]);
                    
                    $prdCouleur = strtolower($prd->getAttributeText('c2c_couleur_eros'));
                    //$prdTaille = $prd->getAttributeText('c2c_taille_pap');
                    if (! $prdCouleur/* || ! $prdTaille*/) {
                        continue;
                    }

                    // Check couleur
                    if (strstr($couleur, $prdCouleur) === false) {
//echo $csvLine[0].' Couleur non trouvée '.$couleur." ".$prdCouleur."\n";
                        continue;
                    }

                    // Check taille
                    /*if (! array_key_exists($taille, $this->_taille)) {
echo $csvLine[0].' Taille non trouvée '.$taille."\n";
                        continue;
                    }

                    if ($this->_taille[$taille] != $prdTaille) {
                        continue;
                    }*/
                    
                    // Update SKU
                    $this->_updatedRows++;
                    //$msg = $csvLine[6]." - $prdCouleur - $prdTaille - (".$prd->getId().") \n";
                    $msg = $csvLine[6]." - $prdCouleur - (".$prd->getId().") \n";
                    echo $msg;
                    Mage::log($msg);
                    $prd->setData('sku', $csvLine[1])
                        //->setData('supplier', self::STARNET_OPTION_ID)
                        ->save();
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

        echo "\n\n Nb lignes mises à jour : ".$this->_updatedRows."\n\n";
        
        return $this;
    }
}

$shell = new Zemode_Shell_BusyxUpdateSku();
$shell->run();
