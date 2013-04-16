<?php
class Magentothem_Newproductslider_Block_Newproductslider extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getNewproductslider()     
    { 
        if (!$this->hasData('newproductslider')) {
            $this->setData('newproductslider', Mage::registry('newproductslider'));
        }
        return $this->getData('newproductslider');
    }
	public function getProducts()
    {
        $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        if (! ($catFilter = Mage::registry('store_category_filter'))) {
            $catFilter = Mage::helper('mostviewedproduct')->getStoreCategoryFilter($_rootcatID);
            Mage::register('store_category_filter', $catFilter);
        }
		
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    	$storeId    = Mage::app()->getStore()->getId();
		$products = Mage::getResourceModel('catalog/product_collection')
			->joinField(
				'category_id',
				'catalog/category_product',
				'',
				'product_id=entity_id',
			    'category_id IN ('.$catFilter.')',
			    'inner'
			)
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addMinimalPrice()
			->addUrlRewrite()
			->addTaxPercents()
			->addStoreFilter()
			->addAttributeToFilter('news_from_date', array('date'=>true, 'to'=> $todayDate))
			->addAttributeToFilter(array(array('attribute'=>'news_to_date', 'date'=>true, 'from'=>$todayDate), array('attribute'=>'news_to_date', 'is' => new Zend_Db_Expr('null'))),'','left')
			->addAttributeToSort('news_from_date','desc');

        $products->getSelect()->distinct(true);
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('newproductslider');
		if (isset($config['newproductslider_config']) ) {
			$value = $config['newproductslider_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}