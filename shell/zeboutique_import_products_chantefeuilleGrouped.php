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
 * @package     Zeboutique_Shell
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Shell
 *
 * @category    Zeboutique
 * @package     Zeboutique_Shell
 * @author      Zeboutique
 */

require_once 'zeboutique_import_products.php';

class Zeboutique_Shell_ImportProducts_ChantefeuilleGrouped extends Zeboutique_Shell_ImportProducts
{
    
    const CHANTEFEUILLE_SUPPLIER_OPTION_ID = 316;
    const CHANTEFEUILLE_MANUFACTURER_OPTION_ID = 321;

    protected $_filename = '2pieces-nv-mb-chantefeuille.csv';

    // C2c_bikini
    protected $_attSet = 35;

    protected $_catIds = array(196);

    protected $_attHautId = 159;
    protected $_attBasId = 160;
    protected $_attTSGId = 163;
    protected $_attCouleurId = 158;

    protected $_attHautCode = 'c2c_haut';
    protected $_attBasCode = 'c2c_bas';
    protected $_attTSGCode = 'taille_soutien_gorge';
    protected $_attCouleurCode = 'c2c_couleur';


    /**
     * Try to update Magento SKU with csv file
     * 
     * @return null
     */
    public function run()
    {
        $io = $this->_getCsvStream();
        
        try {
            $currentMasterSku = null;
            $prdInstance = Mage::getModel('catalog/product');

            $previousImageName = null;
            $position = 0;

            // Configurable associated products
            $simpleProductIds = array();
            $attributeIds = array();

            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $sku = $csvLine[2];
                $simpleSku = $sku;

                // We explode to get master SKU
                $masterSku = explode('-', $sku);
                $masterSku = substr($masterSku[0], 0, -1);

                // If SKU already exists
                if ($prdInstance->getIdBySku($masterSku)) {
                    echo "\n\n SKU grouped $masterSku déjà existante";
                    continue;
                }

echo "masterSku : $masterSku \n";

                // Compare master SKU
                if ($currentMasterSku != $masterSku) {
                    // If no simple product
                    if ($currentMasterSku === null || count($simpleProductIds) == 0) {
                        $currentMasterSku = $masterSku;
echo "\n changement de masterSku \n";
                    } else {
echo "process grouped \n";
                        // Create configurable
                        $masterLabel = trim(array_shift(explode('(', $initLabel)));
                        $masterLabel = ucfirst(trim(substr($masterLabel, 7)));
                        $masterDesc = ucfirst(trim(array_shift(explode('Taille', $prd->getData('description')))));

                        // Set data to grouped
                        $gProduct = Mage::getModel('catalog/product');
                        $gProduct
                            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
                            ->setSku($currentMasterSku)
                            ->setTaxClassId(O)
                            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                            ->setWebsiteIds(array(1))
                            ->setAttributeSetId($this->_attSet)
                            ->setName($masterLabel)
                            ->setShortDescription($masterLabel)
                            ->setDescription($masterDesc)
                            ->setSupplier(self::CHANTEFEUILLE_SUPPLIER_OPTION_ID)
                            ->setManufacturer(self::CHANTEFEUILLE_MANUFACTURER_OPTION_ID)
                            ->setCategoryIds($this->_catIds)
                            ->setData($attCodeCouleur, $couleurId);

                        try {
                            // Set image to grouped
                            $gProduct->addImageToMediaGallery(
                                $this->_dirMediaImport.$previousImageName,
                                array(
                                    'image',
                                    'small_image',
                                    'thumbnail'
                                ),
                                false,
                                false
                            );
                        } catch (Exception $e) {
                            echo "$this->_dirMediaImport.$previousImageName does not exist";
                        }

                        // Add related product
                        $gProduct->setGroupedLinkData($simpleProductIds);

                        $gProduct->setStockData(
                            array(
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 0,
                                'is_in_stock' => 1,
                                'is_salable' => 1
                            )
                        );
                        // Finally save configurable
                        $gProduct->save();
                        $this->_nbConfigurableCreated++;

                        // Change current master SKU
                        $currentMasterSku = $masterSku;

                        // Erase associated
                        $simpleProductIds = array();
                        $attributeIds = array();
                        $position = 0;
                    }
                }
                // End configurable

                // Check if simple product already exists
                $prd = Mage::getModel('catalog/product');
                if ($prdId = $prdInstance->getIdBySku($sku)) {
                    //$prd->load($prdId);
                    // For the moment if simple exists we continue
                    continue;
                } else {
                    $prd->setStoreId(0);
                    $prd->setData('_edit_mode', true);

                    $label = strtolower($csvLine[3]);
                    $initLabel = $csvLine[3];
                    // Regex to find color and size
                    preg_match('@taille.?:.?(.*),.?couleur.?:.?(.*)\)@i', $label, $matches);
                    $taille = strtoupper($matches[1]);
                    $couleur = ucfirst($matches[2]);
                }

                // Right product attribute
                $attCode[0] = $this->_attBasCode;
                $attId[0] = $this->_attBasId;
                if (strstr(strtolower($csvLine[1]), 'haut')) {
                    $attCode[0] = $this->_attHautCode;
                    $attId[0] = $this->_attHautId;
                    $attCode[1] = $this->_attTSGCode;
                    $attId[1] = $this->_attTSGId;
                }

                // Taille
                $attCodeTaille = $attCode[0];
                $attIdTaille = $attId[0];
                $tailleInit = $taille;
                if (! $tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                    $taille = explode('/', $taille);
                    $taille = $taille[0];
                    if (! $tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                        $taille = explode(' ', $taille);
                        $taille = $taille[0];
                        if (! $tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                            $attCodeTaille = $attCode[1];
                            $attIdTaille = $attId[1];
                            $taille = $tailleInit;
                            if (! $tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                                echo "$sku taille NON trouvée : $tailleInit \n";
                                continue;
                            }
                        }
                    }
                }

                $attributeIds['taille'] = $attIdTaille;

                // Couleur
                $attCodeCouleur = $this->_attCouleurCode;
                $attIdCouleur = $this->_attCouleurId;
                if (! $couleurId = $this->_getAttributeOptionIdByValue($attCodeCouleur, $couleur)) {
                    echo "$sku couleur NON trouvée : $couleur \n";
                    continue;
                }
                $attributeIds['couleur'] = $attIdCouleur;

                // Object data
                $prdData = array(
                    $attCodeTaille => $tailleId,
                    $attCodeCouleur => $couleurId,
                    'category_ids' => array(),
                    'sku' => $sku,
                    'name' => $initLabel,
                    'short_description' => $initLabel,
                    'description' => $csvLine[4],
                    'price' => $csvLine[5] * 2,
                    'cost' => $csvLine[5],
                    'image' => 'no_selection',
	                'small_image' => 'no_selection',
	                'thumbnail' => 'no_selection',
                    'weight' => 0,
                    'status' => 1,
                    'visibility' => 1,
                    'supplier' => self::CHANTEFEUILLE_SUPPLIER_OPTION_ID,
                    'manufacturer' => self::CHANTEFEUILLE_MANUFACTURER_OPTION_ID,
                    'tax_class_id' => 0,
                    'stock_data' => array (
                        'qty' => $csvLine[6],
                        'is_in_stock' => 1,
                    ),
                    'website_ids' => array(1)
                );

                $prd->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                    ->setAttributeSetId($this->_attSet)
                    ->addData($prdData);

                // Get image and copy it to media/import dir
                $imageName = array_pop(explode('/', $csvLine[7]));
                if ($previousImageName != $imageName) {
                    $this->_getImageByUrl($csvLine[7], $imageName);
                    $previousImageName = $imageName;
                }

                // Add image to product
                $prd->addImageToMediaGallery(
                    $this->_dirMediaImport.$imageName,
                    array(
                        'image',
                        'small_image',
                        'thumbnail'
                    ),
                    false,
                    false
                );

                $prd->save();
                $this->_nbSimpleCreated++;
                $position++;

                // Add to associated
                $simpleProductIds[$prd->getId()] = array('qty' => 0, 'position' => $position);

                // Display
                echo "ID ".$prd->getId() . " stock ".$csvLine[6] ." \n";
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

        echo "\n\n Nb simples ajoutés : ".$this->_nbSimpleCreated."\n";
        echo "Nb configurables ajoutés : ".$this->_nbConfigurableCreated."\n\n";
        
        return $this;
    }
}

$shell = new Zeboutique_Shell_ImportProducts_ChantefeuilleGrouped();
$shell->run();
