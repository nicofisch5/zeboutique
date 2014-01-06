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
 * @package     Zeboutique_Core
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Starnet
 *
 * @category    Zeboutique
 * @package     Zeboutique_Core
 * @author      Zeboutique
 */
abstract class Zeboutique_Zcore_Model_Import_Product_Abstract extends Mage_Core_Model_Abstract
{
    
    /*protected $_prefix = '';
    protected $_prdIdsInFile = array();*/

    private $_file;
    private $_attributeSetId;
    private $_attributes = array();
    private $_attributeIds = array();
    private $_simpleImages = array();
    private $_availableAttributes = array('couleur', 'taille', 'haut', 'bas');
    private $_attributeOptions = array();
    private $_nbConfigurableCreated = 0;
    private $_nbSimpleCreated = 0;

    protected $_prdData = array();
    protected $_prdIdsToReindex = array();
    protected $_warning = array();

    /*protected function _getRawData() {}
    protected function _prepareData() {}*/


    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_dirMediaImport = Mage::getBaseDir('media').DS.'import'.DS;
    }

    /**
     * Set file
     *
     * @param Varien_Io_File $file
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _setFile($file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * Set file
     *
     * @return Varien_Io_File
     */
    protected function _getFile()
    {
        return $this->_file;
    }

    /**
     * Set attribute set ID
     *
     * @param int $attributeSetId
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _setAttributeSetId($attributeSetId)
    {
        $this->_attributeSetId = $attributeSetId;
        return $this;
    }

    /**
     * Get attribute set ID
     *
     * @return int
     */
    protected function _getAttributeSetId()
    {
        // @todo On estime que le jeu d'attribut a été déterminé dans le formulaire
        return 31; //$this->_attributeSetId;
    }

    /**
     * Retrieve attributes according to attribute set
     *
     * @return array
     */
    protected function _getAttributes()
    {
        if (count($this->_attributes) == 0) {
            $coll = Mage::getModel('eav/entity_attribute')->getCollection()
                        ->setAttributeSetFilter($this->_getAttributeSetId());

            foreach ($coll as $att) {
                if (strpos($att->getData('attribute_code'), 'c2c') !== false) {
                    // Get attribute name
                    list(, $attributeName) = explode('_', $att->getData('attribute_code'));
                    $attributeName = strtolower($attributeName);

                    if (in_array($attributeName, $this->_availableAttributes)) {
                        $this->_attributes[$attributeName] = $att;
                        $this->_attributeIds[$attributeName] = $att->getId();
                    }
                }
            }
        }

        return $this->_attributes;
    }

    /**
     * Add product to BDD
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _processToImportProduct()
    {
        // @todo Choisir le format de données commun
        // masterSku
        // sku
        // name
        // description
        // manufacturer
        // stock
        // price
        // cost
        // weight
        // images
        // attributs

        // Loop pre-requisites
        $prdInstance = Mage::getModel('catalog/product');
        $currentMasterSku = null;
        $previousImageName = null;

        // Configurable associated products
        $simpleProducts = array();

        // Add last line for create last configurable
        $this->_prdData[] = array('---LAST---');

        foreach ($this->_prdData as $lineId => $line) {
            list(
                $masterSku,
                $simpleSku,
                $name,
                $description,
                $manufacturer,
                $stock,
                $price,
                $cost,
                $weight,
                $images,
                $attributs
            ) = $line;

            $masterSku = trim($masterSku);
            $simpleSku = trim($simpleSku);
            $name = ucfirst(trim($name));
            $description = ucfirst(trim($description));

            // If SKU are identical
            if ($simpleSku == $masterSku) {
                $masterSku .= '-configurable';
            }

            // If SKU already exists
            if ($prdInstance->getIdBySku($masterSku)) {
                $this->_warning(Mage::helper('zcore')->__('Configurable SKU %s already exists', $masterSku));
                continue;
            }

echo "<br>masterSku : $masterSku <br>";

            // Compare master SKU
            if ($currentMasterSku != $masterSku) {
echo "<br> changement de masterSku <br>";
                // If no simple product
                if ($currentMasterSku === null || count($simpleProducts) == 0) {
                    $currentMasterSku = $masterSku;
                } else {
echo "process configurable <br>";
                    // Create configurable
                    $masterLabel = $prd->getData('name');
                    $masterDesc = $prd->getData('description');

                    // Set data to configurable
                    $cProduct = Mage::getModel('catalog/product');
                    $cProduct
                        ->setStoreId(0)
                        ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                        ->setSku($currentMasterSku)
                        ->setTaxClassId(0)
                        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->setWebsiteIds(array(1))
                        ->setAttributeSetId($this->_getAttributeSetId())
                        ->setName($masterLabel)
                        ->setShortDescription($masterLabel)
                        ->setDescription($masterDesc)
                        ->setPrice($prd->getPrice())
                        ->setSupplier($this->_supplierId)
                        ->setManufacturer($manufacturer)
                        ->setData('new_imported_product', 1)
// @todo Category IDs
                        //->setCategoryIds()
                        ;

                    $this->_affectImages($cProduct);

                    $cProduct->setCanSaveConfigurableAttributes(true);
                    $cProduct->setCanSaveCustomOptions(true);
                    $cProductTypeInstance = $cProduct->getTypeInstance();
                    $cProductTypeInstance->setUsedProductAttributeIds($this->_attributeIds);

                    // Now we need to get the information back in Magento's own format, and add bits of data to what it gives us.
                    $attributes_array = $cProductTypeInstance->getConfigurableAttributesAsArray($cProduct);
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
                    $this->_nbConfigurableCreated++;

                    // Change current master SKU
                    $currentMasterSku = $masterSku;

                    // Erase associated
                    $simpleProducts = array();
                    $this->_simpleImages = array();
                }
            }
            // End configurable

            if (! $simpleSku || ! $name) {
                continue;
            }

            // Check if simple product already exists
            $prd = Mage::getModel('catalog/product');
            if ($prdId = $prdInstance->getIdBySku($simpleSku)) {
                // For the moment if simple exists we continue
                continue;
            } else {
                $prd->setStoreId(0);
                $prd->setData('_edit_mode', true);
            }

            // Manage attributes
            if ($this->_manageAttributes($attributs, $prd) === false) {
                continue;
            }

            // Object data
            $prdData = array(
                'category_ids' => array(),
                'sku' => $simpleSku,
                'name' => $name,
                'short_description' => $name,
                'description' => $description,
                'price' => $price,
                'cost' => $cost,
                'image' => 'no_selection',
                'small_image' => 'no_selection',
                'thumbnail' => 'no_selection',
                'weight' => $weight,
                'status' => 1,
                'visibility' => 1,
                'supplier' => $this->_supplierId,
                'manufacturer' => $manufacturer,
                'tax_class_id' => 0,
                'stock_data' => array (
                    'qty' => $stock,
                    'is_in_stock' => 1,
                ),
                'website_ids' => array(1),
                'new_imported_product' => 1
            );

            $prd->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                ->setAttributeSetId($this->_getAttributeSetId())
                ->addData($prdData);

            // Get image and copy it to media/import dir
            $this->_getAndAffectImages($images, $prd);

            $prd->save();
            $this->_nbSimpleCreated++;

            // Add to associated
            $simpleProducts[] = $prd;

            $this->_prdIdsToReindex[] = $prd->getId();
        }

        return $this;
    }

    /**
     * Get Attribute Options
     *
     * @param string $attCode
     * @return array
     */
    private function _getAttributeOptions($attCode)
    {
        if (!array_key_exists($attCode, $this->_attributeOptions)) {
            $select = $this->_getSelect()
                ->from(array('a' => $this->_getTable('eav/attribute')))
                ->join(array('ao' => $this->_getTable('eav/attribute_option')), 'ao.attribute_id=a.attribute_id', array('ao.option_id'))
                ->where('a.attribute_code = (?)', $attCode)
                ->joinLeft(
                    array('store' => $this->_getTable('eav/attribute_option_value')),
                    "store.option_id=ao.option_id and store.store_id=0",
                    array('value' => "store.value")
                );

            $rawOptions = $this->_getReadConnection()->fetchAll($select);
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
    private function _getAttributeOptionIdByValue($attCode, $value)
    {
        $options = $this->_getAttributeOptions($attCode);
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }

        return null;
    }

    /**
     * Log info
     * 
     * @param string $msg
     * @param int $level
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _log($msg, $level = Zend_Log::INFO)
    {
        Mage::log(
                $this->_prefix.' - '. $msg,
                $level
        );
       
        return $this;
    }

    /**
     * Warning to display at the end of process
     *
     * @param string $msg
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _warning($msg)
    {
        $this->_warning[] = $msg;
        return $this;
    }

    /**
     * Reindex product data
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _reindexProduct()
    {
        Mage::getResourceSingleton('cataloginventory/indexer_stock')
            ->reindexProducts($this->_prdIdsToReindex);

        return $this;
    }

    /**
     * Before reindex product data
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _beforeReindexProduct()
    {
        return $this;
    }

    /**
     * After reindex product data
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _afterReindexProduct()
    {
        return $this;
    }

    /**
     * Get Select object
     *
     * @return Object
     */
    protected function _getSelect()
    {
        return $this->_getReadConnection()->select();
    }
    
    /**
     * Get read connection
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getReadConnection()
    {
        return $this->_getResource()->getConnection('core_read');
    }

    /**
     * Get write connection
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getWriteConnection()
    {
        return $this->_getResource()->getConnection('core_write');
    }

    /**
     * Get Table
     *
     * @param string $table
     * @return string
     */
    protected function _getTable($table)
    {
        return $this->_getResource()->getTableName($table);
    }
    
    /**
     * Get resource
     *
     * @return Varien_Object
     * @codeCoverageIgnore
     */
    protected function _getResource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * @param string $url
     * @param string $imageName
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _getImageByUrl($url, $imageName)
    {
        copy($url, $this->_dirMediaImport . $imageName);
        return $this;
    }

    /**
     * @param $images
     * @param $prd
     */
    private function _getAndAffectImages($images, $prd)
    {
        foreach ($images as $image) {
            if (!$image) {
                continue;
            }
            $imageExploded = explode('/', $image);
            $imageName = array_pop($imageExploded);

            // Skip existing image
            if (! in_array($imageName, $this->_simpleImages)) {
                $this->_getImageByUrl($image, $imageName);
                $this->_simpleImages[] = $imageName;
            }
        }

        $this->_affectImages($prd);
    }

    /**
     * Affect images to product
     *
     * @param Varien_Object $prd
     */
    private function _affectImages($prd)
    {
        $affectation = array('image', 'small_image', 'thumbnail');
        
        foreach ($this->_simpleImages as $simpleImage) {
            try {
                // Set images to product
                $prd->addImageToMediaGallery(
                    $this->_dirMediaImport . $simpleImage,
                    $affectation,
                    false,
                    false
                );

                $affectation = array();

            } catch (Exception $e) {
                echo "$this->_dirMediaImport.$simpleImage does not exist";
            }
        }
    }

    /**
     * Manage attributes
     *
     * @param array $attributs
     * @param Varien_Object $prd
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    private function _manageAttributes($attributs, $prd)
    {
        if (count($attributs) == 0) {
            return $this;
        }

        foreach ($this->_getAttributes() as $attributeName => $attributeObj) {
            // Taille
            if ($attributeName == 'taille') {
                $taille = $attributs[$attributeName];
                $tailleInit = $taille;

                $attCodeTaille = $attributeObj->getData('attribute_code');
                if (!$tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                    $taille = explode('/', $taille);
                    if (count($taille) > 1) {
                        $taille = $taille[1];
                    } else {
                        $taille = null;
                    }
                    if (!$tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                        $taille = explode(' ', $taille);
                        $taille = $taille[0];
                        if (!$tailleId = $this->_getAttributeOptionIdByValue($attCodeTaille, $taille)) {
                            $this->_warning("taille NON trouvée : $tailleInit");
                            return false;
                        }
                    }
                }

                $prd->setData($attCodeTaille, $tailleId);
            }

            // Couleur
            if ($attributeName == 'couleur') {
                $attCodeCouleur = $attributeObj->getData('attribute_code');
                $couleur = $attributs[$attributeName];

                if (!$couleurId = $this->_getAttributeOptionIdByValue($attCodeCouleur, $couleur)) {
                    $this->_warning("couleur NON trouvée : $couleur");
                    return false;
                }

                $prd->setData($attCodeCouleur, $couleurId);
            }
        }

        return $this;
    }

    /**
     * Finalize process
     *
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    protected function _finalize()
    {
        echo '<br />'.Mage::helper('zcore')->__('Number of configurable created: %', $this->_nbConfigurableCreated);
        echo '<br />'.Mage::helper('zcore')->__('Number of simple created: %', $this->_nbSimpleCreated);

        // Warnings
        echo '<br />'.Mage::helper('zcore')->__('Warnings');
        foreach ($this->_warning as $warning) {
            echo '<br />'.$warning;
        }

        return $this;
    }

    /**
     * Import products entry point
     *FAQ OVERVIEW
     * @return Zeboutique_Zcore_Model_Import_Product_Abstract
     */
    public function importProducts()
    {
        // Get data
        //$this->_getRawData();

        // Prepare data
        $this->_prepareData();

        // Retrieve attributes according to attribute set
        $this->_getAttributes();

        $this->_log(
            Mage::helper('core')->__('Start import product at %s', new Zend_Date())
        );
        // Update stock
        $this->_processToImportProduct();
        $this->_log(
            Mage::helper('core')->__('End import product at %s', new Zend_Date())
        );

        $this->_log(
            Mage::helper('core')->__('Start reindex product at %s', new Zend_Date())
        );

        $this->_beforeReindexProduct();

        // Product reindex
        //$this->_reindexProduct();
        $this->_log(
            Mage::helper('core')->__('End reindex product at %s', new Zend_Date())
        );

        $this->_afterReindexProduct();

        $this->_finalize();

        return $this;
    }
}