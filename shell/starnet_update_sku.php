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
    
    const FILENAME = 'stock_starnet.csv';
    const URL_STOCK_FILE = 'http://www.starnet-world.com/stock_starnet.csv';
    const URL_TARIF_FILE = 'http://www.starnet-world.com/files/tarifs_clients_2013.xlsx';
    const STARNET_OPTION_ID = 311;

    // C2c_Taille
    //protected $_attSet = 36;

    // C2c_Couleur
    protected $_attSet = 38;
    
    /*protected $_taille = array(
        'ps' => 'Grande Taille Unique',
        'l' => 'L-40',
        'lxl' => 'L/XL',
        'os' => 'Taille Unique',
        'XXXXL-48',
        'm' => 'M-38',
        'ml' => 'M/L',
        's' => 'S-36',
        'sm' => 'S/M',
        'xl' => 'XL-42',
        'xs' => 'XS-34',
        'xxl' => 'XXL-44',
        'xxlxxxl' => 'XXXL-46'
    );*/

    protected $_taille = array(
        'ps' => 'Grande Taille Unique',
        'os' => 'Taille Unique',
        'sl' => 'L - 40',
        'l' => 'L - 40',
        'lxl' => 'L/XL 40-42',
        'os' => 'Taille Unique',
        'm' => 'M - 38',
        'ml' => 'M/L 38-40',
        's' => 'S - 36',
        'sm' => 'S/M 36-38',
        'xl' => 'XL - 42',
        'xs' => 'XS - 34',
        'xxl' => 'XXL - 44',
        'xxxl' => 'XXXL - 46',
        'xxlxxxl' => '2X-3X',

        '1x2x' => '1X-2X',
        '3x-4x' => '3X-4X',
        '1x' => '1x',
        '2x' => '2x'
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

                $csvLine[0] = trim($csvLine[0]);
                
                // Products that begin with starnet SKU master
                $coll = Mage::getModel('catalog/product')->getCollection()
                            ->addFieldToFilter('type_id', 'simple')
                            ->addFieldToFilter('sku', array('like'=>$csvLine[0].'%'))
                            ->addFieldToFilter('attribute_set_id', $this->_attSet);

                // Skip line if no product in Magento
                if ($coll->getSize() == 0) {
                    //echo "Ref non trouvée ".$csvLine[0]." \n";
                    continue;
                }

                // Parse products collection
                foreach ($coll as $prd) {
                    $prd->load($prd->getId());

                    $prdCouleur = strtolower($prd->getAttributeText('c2c_couleur'));
                    //$prdTaille = $prd->getAttributeText('c2c_taille');
                    if (! $prdCouleur/* || ! $prdTaille*/) {
                        continue;
                    }

                    /***** DEBUT CAS DOUBLE COULEUR *****/
                    preg_match('@couleur.?:.?(.*)\/(.*) \/.?taille.?:.?(.*)@i', $csvLine[2], $matches);
                    if (count($matches) > 3) {
                        // Cas double couleur confirmé
                        $couleur1 = strtolower($matches[1]);
                        $couleur2 = strtolower($matches[2]);
                        $taille = strtolower($matches[3]);

                        if (strstr($prdCouleur, 'et') === false) {
                            continue;
                        }
    //echo $prdCouleur." \n";

                        if (!(strstr($prdCouleur, $couleur1) !== false && strstr($prdCouleur, $couleur2) !== false)) {
                            continue;
                        }
                    } else {
                        /***** DEBUT CAS CLASSIQUE *****/

                        preg_match('@couleur.?:.?(.*) \/.?taille.?:.?(.*)@i', $csvLine[2], $matches);
                        //preg_match('@couleur.?:.?(.*)@i', $csvLine[2], $matches);
                        $couleur = strtolower($matches[1]);
                        $taille = strtolower($matches[2]);

                        // Check couleur
                        if (strstr($couleur, $prdCouleur) === false) {
                            //echo $csvLine[0].' Couleur non trouvée '.$couleur." ".$prdCouleur."(".$prd->getData('sku').") \n";
                            continue;
                        }

                        /***** FIN CAS CLASSIQUE *****/
                    }

                    // Check taille
                    /*if (! array_key_exists($taille, $this->_taille)) {
                        //echo '----- Taille non trouvée '.$taille."(".$prd->getData('sku').") \n";
                        continue;
                    }

                    if ($this->_taille[$taille] != $prdTaille) {
                        continue;
                    }*/

                    if ($prd->getData('sku') == $csvLine[1]) {
                        //echo "----- SKU ".$prd->getData('sku')." déjà modfiée \n";
                        continue;
                    }

                    // Update SKU
                    $this->_updatedRows++;
                    $msg = "Old SKU ".$prd->getData('sku'). " New ".$csvLine[1]." - $prdTaille - $prdCouleur - (".$prd->getId().") \n";
                    //$msg = $csvLine[2]." - $prdCouleur - (".$prd->getId().") \n";
                    echo $msg;
                    /*Mage::log($msg);
                    $prd->setData('sku', $csvLine[1])
                        ->setData('starnet_sku_updated', 1)
                        ->setData('supplier', self::STARNET_OPTION_ID)
                        ->save();*/
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

$shell = new Zemode_Shell_StarnetUpdateSku();
$shell->run();
