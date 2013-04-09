<?php
class Magentothem_Featuredproductvertscroller_Block_Featuredproductvertscroller extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFeaturedproductvertscroller()     
     { 
        if (!$this->hasData('featuredproductvertscroller')) {
            $this->setData('featuredproductvertscroller', Mage::registry('featuredproductvertscroller'));
        }
        return $this->getData('featuredproductvertscroller');
        
    }
	public function getProducts()
    {
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
    	$storeId    = Mage::app()->getStore()->getId();
		$products = Mage::getResourceModel('catalog/product_collection')
			->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addMinimalPrice()
			->addStoreFilter()
			->setOrder($this->getConfig('sort'),$this->getConfig('direction'))
			->addAttributeToFilter('category_id', array('in' => $_rootcatID))
			->addAttributeToFilter("featured", 1);		
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('qty'))->setCurPage(1);
        $this->setProductCollection($products);
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('featuredproductvertscroller');
		if (isset($config['featuredproductvertscroller_config']) ) {
			$value = $config['featuredproductvertscroller_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}