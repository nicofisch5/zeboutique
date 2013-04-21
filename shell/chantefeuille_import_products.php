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
class Zeboutique_Shell_ChantefeuilleImportProducts extends Mage_Shell_Abstract
{
    
    const FILENAME = 'mb-chantefeuille.csv';
    const CHANTEFEUILLE_SUPPLIER_OPTION_ID = 316;
    const CHANTEFEUILLE_MANUFACTURER_OPTION_ID = 321;

    protected $_updatedRows = 0;

    // C2c_bikini
    protected $_attSet = 35;

    protected $_attHautId = 159;
    protected $_attBasId = 160;
    protected $_attTSGId = 163;
    protected $_attCouleurId = 158;

    protected $_attHautCode = 'c2c_haut';
    protected $_attBasCode = 'c2c_bas';
    protected $_attTSGCode = 'taille_soutien_gorge';
    protected $_attCouleurCode = 'c2c_couleur';
  
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
        $io->streamReadCsv();
        
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
        $io = $this->_getCsvStream();
        
        try {
            $rowNumber  = 0;
            $currentMasterSku = null;
            $prdInstance = Mage::getModel('catalog/product');
            $dirMediaImport = Mage::getBaseDir('media').DS.'import'.DS;

            $previousImageName = null;

            // Configurable associated products
            $simpleProducts = array();
            $attributeIds = array();

            while (false !== ($csvLine = $io->streamReadCsv(";"))) {
                $rowNumber++;

                $sku = $csvLine[2];
                $simpleSku = $sku;

                /**
                 * 1. Décortiquer le sku en splitant au premier caratère "-"
                 * 2.1. Tant que la racine du SKU est la même on créé le produit simple et on stock son ID
                 * 2.2. Si la racine change on rentre dans les cas suivants :
                 *     2.2.1. Aucun produit simple n'a été créé (prob attribut)
                 *              -> on change de master SKU et on passe au prd simple suivant
                 *     2.2.2. Au moins 1 produit simple a été créé
                 *              -> on créé le configurable, on change de master SKU et on passe au prd simple suivant
                 */

                // We explode to get master SKU
                $masterSku = explode('-', $sku);
                $masterSku = $masterSku[0];

                if ($simpleSku == $masterSku) {
                    $masterSku .= '-configurable';
                }
echo "masterSku : $masterSku \n";

                // Compare master SKU
                if ($currentMasterSku != $masterSku) {
                    // If no simple product
                    if ($currentMasterSku === null || count($simpleProducts) == 0) {
                        $currentMasterSku = $masterSku;
echo "\n changement de masterSku \n";
                    } else {
echo "process configurable \n";
                        // Create configurable
                        $masterLabel = ucfirst(trim(array_shift(explode('(', $initLabel))));
                        $masterDesc = ucfirst(trim(array_shift(explode('Taille', $prd->getData('description')))));

                        // Set data to configurable
                        $cProduct = Mage::getModel('catalog/product');
                        $cProduct
                            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                            ->setSku($currentMasterSku)
                            ->setTaxClassId(O)
                            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                            ->setWebsiteIds(array(1))
                            ->setAttributeSetId($this->_attSet)
                            ->setName($masterLabel)
                            ->setShortDescription($masterLabel)
                            ->setDescription($masterDesc)
                            ->setPrice($prd->getPrice())
                            ->setSupplier(self::CHANTEFEUILLE_SUPPLIER_OPTION_ID)
                            ->setManufacturer(self::CHANTEFEUILLE_MANUFACTURER_OPTION_ID);

                        // Set image to configurable
                        $cProduct->addImageToMediaGallery(
                            $dirMediaImport.$previousImageName,
                            array(
                                'image',
                                'small_image',
                                'thumbnail'
                            ),
                            false,
                            false
                        );

                        $cProduct->setCanSaveConfigurableAttributes(true);
                        $cProduct->setCanSaveCustomOptions(true);
                        $cProductTypeInstance = $cProduct->getTypeInstance();
                        // This array is an array of attribute ID's which the configurable product swings around (i.e; where you say when you
                        // create a configurable product in the admin area what attributes to use as options)
                        // $_attributeIds is an array which maps the attribute(s) used for configuration so their numerical counterparts.
                        // (there's probably a better way of doing this, but i was lazy, and it saved extra db calls);
                        // $_attributeIds = array("size" => 999, "color", => 1000, "material" => 1001);
                        // etc..
                        /*$cProductTypeInstance->setUsedProductAttributeIds(
                            array($attributeIds[$configurable_attribute])
                        );*/
                        $cProductTypeInstance->setUsedProductAttributeIds($attributeIds);

                        // Now we need to get the information back in Magento's own format, and add bits of data to what it gives us.
                        $attributes_array = $cProductTypeInstance->getConfigurableAttributesAsArray();
                        foreach($attributes_array as $key => $attribute_array) {
                            $attributes_array[$key]['use_default'] = 1;
                            $attributes_array[$key]['position'] = 0;
                            if (isset($attribute_array['frontend_label'])) {
                                $attributes_array[$key]['label'] = $attribute_array['frontend_label'];
                            } else {
                                $attributes_array[$key]['label'] = $attribute_array['attribute_code'];
                            }
                        }
                        // Add it back to the configurable product.
                        $cProduct->setConfigurableAttributesData($attributes_array);
                        // Remember that $simpleProducts array we created earlier? Now we need that data
                        $dataArray = array();
                        foreach ($simpleProducts as $simplePrd) {
                            $dataArray[$simplePrd->getId()] = array();
                            foreach ($attributes_array as $attrArray) {
                                array_push(
                                    $dataArray[$simplePrd->getId()],
                                    array(
                                        "attribute_id" => $attrArray['attribute_id'],
                                        "label" => $attrArray['frontend_label'],
                                        "is_percent" => false,
                                        "pricing_value" => $simplePrd->getPrice()
                                    )
                                );
                            }
                        }
                        // This tells Magento to associate the given simple products to this configurable product..
                        $cProduct->setConfigurableProductsData($dataArray);
                        // Set stock data. Yes, it needs stock data. No qty, but we need to tell it to manage stock, and that it's actually
                        // in stock, else we'll end up with problems later
                        $cProduct->setStockData(
                            array(
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 0,
                                'is_in_stock' => 1,
                                'is_salable' => 1
                            )
                        );
                        // Finally save configurable
                        $cProduct->save();

                        // Change current master SKU
                        $currentMasterSku = $masterSku;

                        // Erase associated
                        $simpleProducts = array();
                        $attributeIds = array();
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
                    copy($csvLine[7], $dirMediaImport.$imageName);
                    $previousImageName = $imageName;
                }

                // Add image to product
                $prd->addImageToMediaGallery(
                    $dirMediaImport.$imageName,
                    array(
                        'image',
                        'small_image',
                        'thumbnail'
                    ),
                    false,
                    false
                );

                $prd->save();

                // Add to associated
                /*array_push(
                    $simpleProducts,
                    array(
                        "id" => $prd->getId(),
                        "price" => $prd->getPrice(),
                        'attribute' => array(
                            array(
                                "attr_code" => $attCodeTaille,
                                "attr_id" => $attIdTaille,
                                "value" => $tailleId,
                                "label" => 'Taille'
                            ),
                            array(
                                "attr_code" => $attCodeCouleur,
                                "attr_id" => $attIdCouleur,
                                "value" => $couleurId,
                                "label" => 'Couleur'
                            )
                        )
                    )
                );*/
                $simpleProducts[] = $prd;

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

        echo "\n\n Nb prds ajoutés : ".$this->_updatedRows."\n\n";
        
        return $this;
    }
}

$shell = new Zeboutique_Shell_ChantefeuilleImportProducts();
$shell->run();
