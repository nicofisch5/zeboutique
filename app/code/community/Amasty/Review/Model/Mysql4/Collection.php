<?php
/**
* @copyright Amasty.
*/ 
class Amasty_Review_Model_Mysql4_Collection extends Mage_Review_Model_Mysql4_Review_Product_Collection
{
    // approved reviews plus visible in catalog products plus url revrites data
    public function addVisiblityFilter()
    {
        $store = Mage::app()->getStore();
        
        $this->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addStoreFilter($store->getId()) 
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect(array('name','visibility'), 'inner');
            
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this);   
        
        $urCondions = array(
            'e.entity_id=ur.product_id',
            'ur.category_id IS NULL',
            'ur.store_id='.intVal($store->getId()),
            'ur.is_system=1'
        );
        
        $this->getSelect()->joinLeft(
            array('ur' => $this->getTable('core/url_rewrite')),
            join(' AND ', $urCondions),
            array('url' => 'request_path')
        );        
         
        return $this;
    }
    
    public function addRatingData()
    {
        $ratingTable = Mage::getSingleton('core/resource')->getTableName('rating/rating_option_vote');
        $this->getSelect()
            ->joinLeft(array('rat' => $ratingTable),
                'rat.review_id = rt.review_id',
                array('av_rating' => new Zend_Db_Expr('AVG(rat.percent)')))
            ->group('rt.review_id');

        return $this;        
    }
        
} 