<?php
class Dredd_Comparer_Helper_Collect extends Mage_Core_Helper_Abstract
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

    public function _isTax($store_id)
    {

        //si le prix est affiché en both (HT et TTC)                                TTC
        if((Mage::helper('tax')->getPriceDisplayType($store_id) == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH) || (Mage::helper('tax')->getPriceDisplayType($store_id) == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX)){
            if(Mage::helper('tax')->priceIncludesTax($store_id)){
                return false;
            }
            else{
                return true;
            }
        }
        return false;
    }
    /**
     * recuperer tous les attributs de produit
     * @return array
     */
    public function recupAttribProduct($entityTypeId)
    {
        $attribProd = array();
        $tableName = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        	
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
        ->from($tableName, array('attribute_code'))
        ->where('entity_type_id=?',$entityTypeId);
        $val = $read->fetchAll($select);

        if(isset($val) && !empty($val)){
            foreach($val as $ligne){
                foreach($ligne as $cle=>$value){
                    array_push($attribProd, $value);
                }
            }
            return $attribProd;
        }
    }

    public function getUrlImage($product)
    {
        $url = false;

        if ($product->getImage()) {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/'.$product->getImage();
        }
        else{
            $url = Mage::getDesign()->getSkinUrl('images/no_image.jpg');
        }
        return $url;
    }

    /**
     *
     * @param <type> $comparer
     * @param <type> $separator
     * @param <type> $lines         les lignes de mappage
     * @param <type> $t_pId         tableau contenant 100 id de produit
     * @return <type> bloc de 100 lignes à enregistrer dans le fichier
     */
    public function generateLineCollect($comparer, $separator, $lines, $t_pId, $entityTypeId)
    {
         

        $attribProd = $this->recupAttribProduct($entityTypeId);
        $tracking = $comparer->getData('tracking');
        $category_ids = $comparer->getCategoryIds();
        $now = Mage::getModel('core/date')->timestamp(time());


        $store_id = $comparer->getStoreId();
        $csv = '';
        $bloc_csv = '';

        $products = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect('*')
                        ->addIdFilter($t_pId)
                        ->addStoreFilter($store_id)
                        ->addAttributeToFilter('status',1);

        foreach($products as $product)
        {
            if($product->getId())
            {

                /****************************** VERIF STOCK **************************************/
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
                        if ($stockItem -> getData('is_in_stock') == 0 || $stockItem -> getData('is_in_stock') <= 0)
                        return "";
                    }
                }
                /**************************** FIN VERIF STOCK ************************************/
                /******************************* PRICE RULE **************************************/

                $websiteId = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
                $product->setWebsiteId($websiteId);
                $product->setStoreId($store_id);
                $product->setCustomerGroupId(0);
                //Mage::dispatchEvent('catalog_product_get_final_price', array('product'=>$product));

                //-----------------------------begin price rule-----------------------------------
                $product->setData('final_price', $product->getPrice());

                $special = $product->getResource()->getAttribute('special_price')->getFrontend()->getValue($product);
                if(isset($special) && ($special != null)){
                    $from = $product->getResource()->getAttribute('special_from_date')->getFrontend()->getValue($product);
                    $to = $product->getResource()->getAttribute('special_to_date')->getFrontend()->getValue($product);
                    $zao = date('Y-m-d h:i:s', $now);

                    if((strcmp($from, $zao) < 0) && (isset($to)? strcmp($to, $zao) : 1)) {
                        $prix_zao = $special;
                        $product->setData('final_price', $special);
                    }
                    else{
                        $prix_zao = $product->getPrice();
                    }
                }
                $special = null;


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
                    if ($this->_rulePrices[$key] !== false) {
                        $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                        $product->setFinalPrice($finalPrice);
                    }
                }

                /***************************** END PRICE RULE ************************************/

                $t_csvline = array();
                foreach($lines as $line){

                    $attribute_code = $line->getAttributeCode();
                    $default_value = $line->getDefaultValue();
                    $value = '';
                    $length = ($line->getMaxSize()) ? $line->getMaxSize() : 0;
                    switch($attribute_code){

                        case 'none' :
                            $value = $default_value;
                            break;

                        case 'product_id' :
                            $value = $product->getId();
                            break;

                        case 'category' :
                        case 'category_ids' :
                            $t_category_ids = explode(',', $category_ids);
                            $product_cat_ids = $product->getCategoryIds();
                             
                            $t = array(0);
                            foreach($t_category_ids as $categoryId){
                                if(in_array($categoryId, $product_cat_ids)) {
                                    $t[] = $categoryId;
                                }
                            }

                            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                            $categories = Mage::getResourceModel('catalog/category_collection')
                                            ->addAttributeToSelect('name')
                                            ->addFieldToFilter('level', array('gt'=>1))
                                            ->setStoreId($store_id)
                                            ->addIdFilter($t)
                                            ->setOrder('level', 'ASC')
                                            ->setOrder('position', 'ASC');
                            $value = (string)$categories->getFirstItem()->getName();
                            $value = Mage::helper('comparer')->formatHTML($value, $length);
                            break;

                        case 'description' :
                            $value = $product->getResource()->getAttribute('description')->getFrontend()->getValue($product);
                            $value = Mage::helper('comparer')->formatHTML($value, $length);
                            break;
                        case 'short_description' :
                            $value = $product->getResource()->getAttribute('short_description')->getFrontend()->getValue($product);
                            $value = Mage::helper('comparer')->formatHTML($value, $length);
                            break;

                        case 'stock_qty' :
                            $value = $stockItem->getQty();
                            break;

                        case 'current_price' :
                            $value = ( Mage::helper('comparer/csv')->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                            $value = sprintf("%01.2f", $value);
                            break;

                        case 'promo_price' :
                            $prix_promo = '';
                            $final_price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                            $price = ( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getPrice(), true) : $product->getPrice();

                            if ($final_price < $price) {
                                $prix_promo = $final_price;
                            }
                            $value = $prix_promo;
                            $value = sprintf("%01.2f", $value);
                            break;

                        case 'normal_price' :

                            $prix_barre = '';
                            $final_price = (float)( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getFinalPrice();
                            $price = (float)( $this->_isTax($store_id) ) ? Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true) : $product->getPrice();

                            if ($final_price < $price) {
                                $prix_barre = $price;
                            }
                            $value = $prix_barre;
                            $value = sprintf("%01.2f", $value);
                            break;

                        case 'image_url' :
                            $value = $this->getUrlImage($product);
                            //$value = (string)Mage::helper('catalog/product')->getImageUrl($product);
                            $value = str_ireplace('https://', 'http://', $value);
                            $value = str_ireplace('product//', 'product/', $value);
                            break;

                        case Dredd_Comparer_Model_Mappage::C_PRODUCT_URL :
                             //  $value = Mage::getModel('catalog/product_url')->getProductUrl($product);
                            //$value = $product->getProductUrl(false) . $this->_generateTracking($tracking, $product);
							$value = Mage::getModel('catalog/product_url')->getProductUrl($product) . $this->_generateTracking($tracking, $product);
                            break;

                        case 'currency' :
                            $value = Mage::getStoreConfig('currency/options/base');
                            break;

                        default :
                            $value = '';
                            if(in_array($attribute_code, $attribProd)){
                                $value = $product->getResource()->getAttribute($attribute_code)->getFrontend()->getValue($product);
                                if(isset($value) && !empty($value)){
                                    if(is_array($value))
                                    $value = $value['label'];
                                    else
                                    $value = $value;
                                }
                            }
                            $value = Mage::helper('comparer')->formatHTML($value, $length);
                    }

                    if (empty($value)) $value = $default_value;
                    $value = utf8_decode($value);

                    $t_csvline[] = $value;
                }
                $csv .= "\r\n";
                $csv .= implode($separator, $t_csvline);

                $bloc_csv .= $csv;
                $csv = '';
            }
            else{
                continue;
            }
        }
        return $bloc_csv;
    }

}
