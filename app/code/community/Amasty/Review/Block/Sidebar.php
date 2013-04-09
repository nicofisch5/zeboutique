<?php
/**
* @copyright Amasty.
*/ 
class Amasty_Review_Block_Sidebar extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if (!Mage::getStoreConfig('amreview/sidebar/active'))
            return parent::_prepareLayout();
            
        $count = Mage::getStoreConfig('amreview/sidebar/count');
        if (!$count)
            return parent::_prepareLayout();
        
        $collection = Mage::getResourceModel('amreview/collection')
            ->addVisiblityFilter()
            ->setPageSize($count);
        if (Mage::getStoreConfig('amreview/sidebar/show_stars'))
            $collection->addRatingData(); 
           
        if (Mage::getStoreConfig('amreview/sidebar/by_date'))   
            $collection->setDateOrder();
        else 
            $collection->getSelect()->order('rand()');

        // sinse v1.0.6    
        $currProduct   = Mage::registry('product');
        $currCategory  = Mage::registry('current_category');
        
        if (Mage::getStoreConfig('amreview/sidebar/for_product') && $currProduct instanceof Mage_Catalog_Model_Product){
            $collection->addEntityFilter($currProduct->getId());
        }
        elseif (Mage::getStoreConfig('amreview/sidebar/for_category') && $currCategory instanceof Mage_Catalog_Model_Category){
            $collection->addCategoryFilter($currCategory);
        }             
          
        //echo  $collection->getSelect(); exit; 
        $collection->load();
            
            
        $baseUrl = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        foreach ($collection->getItems() as $product){
            $url = Mage::getUrl('catalog/product/view', array('id'=>$product->getId()));
            if ($product->getUrl())
                $url = $baseUrl . $product->getUrl();
            $product->setUrl($url);
            
            $product->setDetail($this->_trim($product->getDetail()));
            
            if (Mage::getStoreConfig('amreview/sidebar/show_stars')){
                $vote = new Varien_Object();
                $vote->setPercent($product->getAvRating());
                $vote->setRatingCode(Mage::helper('amreview')->__('Rating'));
                $product->setRatingVotes(array($vote));
            }        
        }  
        
        $this->setReviews($collection);
        return parent::_prepareLayout();
    }
    
    
    protected function _trim($str)
    {
        $max = Mage::getStoreConfig('amreview/sidebar/max_words');
        if ($max > 0){
            $str = implode(' ', array_slice(preg_split('/\s+/', $str), 0, $max)); 
        } 
        return $str;
    }
}