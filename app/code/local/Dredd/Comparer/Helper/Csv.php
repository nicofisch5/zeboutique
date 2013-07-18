<?php
class Dredd_Comparer_Helper_Csv extends Mage_Core_Helper_Abstract
{
    protected $_rulePrices = array();
    var $totalTemps = 0;

    public function _generateTracking($codeTracking, $product)
    {
        $strcodeTrack = '';
        $codeTrack = $codeTracking;
        $codeTrack = str_replace('pdt_sku_and_name', $product->getSku().'-'.$product->getName(), $codeTrack);
        $codeTrack = str_replace('pdt_id_and_name', $product->getId().'-'.$product->getName(), $codeTrack);
        if(trim($codeTrack)!='') {
            $strcodeTrack .= '?'.$codeTrack;
            return $strcodeTrack;
        }
        return '';
    }

    public function _getAttributeValue($attributes, $product, $code)
    {
        $val = null;
        foreach($attributes as $attribute){
            if($attribute->getAttributeCode() == $code){
                $val = $attribute->getFrontend()->getValue($product);
                if(isset($val) && !empty($val)){
                    if (is_array($val)){
                        return $val['label'];
                    }
                    else{
                        return $val;
                    }
                }
                else{
                    return '';
                }
            }
        }
        return 'missing attribute';
    }


    public function _isTax($store_id)
    {
        //si le prix est affich� en both (HT et TTC)
        if (Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, $store_id) == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH){
            //Si le prix a d�j� un taxe
            if (Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store_id) == 1){
                return false;
            }else{
                return true;
            }
        }

        //si afficher le prix avec tax
        if (Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, $store_id) == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX) {
            //Si le prix a d�j� un taxe
            if (Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store_id) == 1){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }


    protected function _recupValueViaFlatCatalogue($id)
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_flat_1');
        	
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
        ->from($tableName)
        ->where('entity_id=?',$id);
        $val = $read->fetchAll($select);

        if(isset($val) && !empty($val)){
            foreach($val as $ligne){
                foreach($ligne as $cle=>$value){
                }
            }
        }
        return 'tableau des valeurs';
    }


    public function generateLine($comparer, $separator, $lines, $pId)
    {
        	
        $csv = '';

        $tracking = $comparer->getData('tracking');
        $store_id = $comparer->getStoreId();
        $category_ids = $comparer->getCategoryIds();

        $product = Mage::getModel('catalog/product')
        ->setStoreId($comparer->getStoreId())
        ->load($pId);

        //test ID : cas de produit supprim�
        if (!$product->getId()) return "";

        //test status : cas de produit d�sactiv�
        if (!$product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) return "";

        //test visibility : cas de produit visible nulle part
        if ($product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) return "";

        //test stock
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($product->getId());
        //en stock
        if ($comparer->getData('stock_param') == Dredd_Comparer_Model_Comparer::EXPORT_STOCK){
            //si gerer stock
            if ($stockItem -> getData('manage_stock') == 0) return "";
            if ($stockItem -> getData('manage_stock') == 1){
                //si c pas en stock OR si qty <=0
                if ($stockItem -> getData('is_in_stock') == 0 || $stockItem -> getData('is_in_stock') <= 0) return "";
            }
        }
        //en stock & stock not managed
        if ($comparer->getData('stock_param') == Dredd_Comparer_Model_Comparer::EXPORT_STOCK_STOCKNOTMANAGED){
            //si gerer stock
            if ($stockItem -> getData('manage_stock') == 1){
                //si c pas en stock OR si qty <=0
                if ($stockItem -> getData('is_in_stock') == 0 || $stockItem -> getData('is_in_stock') <= 0) return "";
            }
            //sinon laisser la boucle

        }

        /**
         * Dispatch event pour avoir final_price
         */
        $websiteId = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
        $product->setWebsiteId($websiteId);
        $product->setCustomerGroupId(0);
        //Mage::dispatchEvent('catalog_product_get_final_price', array('product'=>$product));

        ///-------------------begin price rule-----------------------------------------------------
        $product->setData('final_price', $product->getPrice());
        $pId        = $product->getId();
        $date 		= Mage::app()->getLocale()->storeTimeStamp($store_id);
        $wId 		= $websiteId;
        $gId 		= (!is_null($product->getCustomerGroupId())) ? $product->getCustomerGroupId() : 0;
        $key 		= "$date|$wId|$gId|$pId";
        if ($key) {

            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice($date, $wId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key]!==false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }
        ///-------------------end price rule-----------------------------------------------------

        $attributes = $product->getAttributes();


        $t_csvline = array();
        foreach($lines as $line){
            $attribute_code = $line->getAttributeCode();

            $default_value = $line->getDefaultValue();
            $value = '';
            $length = ($line->getMaxSize()) ? $line->getMaxSize() : 0;

            switch($attribute_code){

                case Dredd_Comparer_Model_Mappage::C_NONE :
                    $value = $default_value;
                    break;

                case Dredd_Comparer_Model_Mappage::C_STOCK_QTY :
                    $value = $stockItem->getQty();
                    break;

                case Dredd_Comparer_Model_Mappage::C_IMAGE_URL :
                    $value = (string)Mage::helper('catalog/product')->getImageUrl($product);
                    $value = str_ireplace('https://', 'http://', $value);
                    break;

                case Dredd_Comparer_Model_Mappage::C_PRODUCT_URL :
                    $value = $product->getProductUrl(false) . $this->_generateTracking($tracking, $product);
                    break;

                case Dredd_Comparer_Model_Mappage::C_CATEGORY :
                case 'category_ids' :
                    $value = '';

                    $t_category_ids = explode(',', $category_ids);
                    $product_cat_ids = $product->getCategoryIds();		//un produit peut etre dans pls categorie
                    $t = array(0);
                    foreach($t_category_ids as $categoryId){
                        if(in_array($categoryId, $product_cat_ids)) { //Si la categorie � � celle du produit
                            $t[] = $categoryId;
                        }
                    }

                    $categories = Mage::getModel('catalog/category')->getCollection()
                    ->setProductStoreId($store_id)
                    ->addAttributeToSelect('name')
                    ->addFieldToFilter('level', array('gt'=>1))
                    ->addFieldToFilter('entity_id', array('in'=>$t))
                    ->setOrder('level', 'ASC')
                    ->setOrder('position', 'ASC');

                    $value = $categories->getFirstItem()->getName();
                    break;

                case Dredd_Comparer_Model_Mappage::C_PRODUCT_ID :
                    $value = $product->getId();
                    break;

                case Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE :
                    $value = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                    $value = sprintf("%01.2f", $value);
                    break;

                case Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE_PROMO :
                    $prix_promo = '';
                    $final_price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                    $price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getPrice(), true) : $product->getPrice();
                    if ($final_price < $price) {
                        $prix_promo = $final_price;
                    }
                    $value = $prix_promo;
                    $value = sprintf("%01.2f", $value);
                    break;

                case Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE_BARRE :
                    $prix_barre = '';
                    $final_price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                    $price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getPrice();
                    if ($final_price < $price) {
                        $prix_barre = $price;
                    }
                    $value = $prix_barre;
                    $value = sprintf("%01.2f", $value);
                    break;

                case 'price':
                case 'special_price':
                    $value = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getData($attribute_code), true) : $product->getData($attribute_code);
                    $value = sprintf("%01.2f", $value);
                    break;

                case Dredd_Comparer_Model_Mappage::C_CURRENCY :
                    $value = Mage::getStoreConfig('currency/options/base');
                    break;

                case 'description':
                case 'short_description':
                    $value = $this->_getAttributeValue($attributes, $product, $attribute_code);
                    	
                    $value = Mage::helper('comparer')->formatHTML($value, $length);
                    break;

                default :

                    $valeur = $this->_getAttributeValue($attributes, $product, $attribute_code);
                    $value = $valeur;
            }

            if (empty($value)) $value = $default_value;
            $value = utf8_decode($value);
            	
            $t_csvline[] = $value;

        }
        $csv .= implode($separator, $t_csvline);
        return $csv;
    }

    public function generateLine1($comparer, $separator, $lines, $pId, $t_mappage)
    {
        $storeId = $comparer->getStoreId();
        $csv = '';
        $t_csvline = array();
        $t_attribFlat = array();

        $t_attr_in_flat = array();
        $t_attr_not_in_flat = array();
        $reste = array();

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName("catalog_product_flat_{$storeId}");
        	
        $select = $read->select()
        ->from($tableName)
        ->where('entity_id=?',$pId);
        $val = $read->fetchAll($select);

        if(isset($val) && !empty($val)){
            foreach($val as $ligne){
                foreach($ligne as $cle=>$value){

                    if(in_array($cle, $t_mappage)){
                        $value = utf8_decode($value);
                        array_push($t_csvline, $value);
                    }
                    else{
                        continue;
                    }

                }
                $csv .= implode($separator, $t_csvline);

            }
            return $csv;
        }
        else{
            return '';
        }
    }
}