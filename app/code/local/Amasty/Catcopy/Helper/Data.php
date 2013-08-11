<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSearchReplaceCnt()
    {
        return 10;
    }
    
    public function suggestUrlKey($urlKey)
    {
        return $urlKey;
        
        /*if (preg_match('@([^\d]+)(\d+)$@', $urlKey, $matches))
        {
            if (isset($matches[2]))
            {
                $urlKey = substr($urlKey, 0, -strlen($matches[2]));
                $urlKey = $urlKey . ++$matches[2];
                return $urlKey;
            }
        }
        return $urlKey . '-1';*/
    }
    
    public function copyStoreData($fromCategoryId, $toCategoryId)
    {
        $dataToCopy = array(
            'name',
            'description',
            'display_mode',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'is_active',
            'custom_layout_update',
            'available_sort_by',
        );
        
        // with no store first:
        $fromCategory = Mage::getModel('catalog/category')->load($fromCategoryId);
        $toCategory   = Mage::getModel('catalog/category')->load($toCategoryId);
        foreach ($dataToCopy as $field)
        {
            if (!is_null($fromCategory->getData($field)))
            {
                $toCategory->setData($field, $fromCategory->getData($field));
            }
        }
        $this->searchAndReplace($toCategory);
        $toCategory->save();
        
        // then per store:
        $stores = Mage::app()->getStores();
        if (!empty($stores))
        {
            foreach ($stores as $store)
            {
                $fromCategory = Mage::getModel('catalog/category')->setStoreId($store->getId())->load($fromCategoryId);
                $toCategory   = Mage::getModel('catalog/category')->setStoreId($store->getId())->load($toCategoryId);
                foreach ($dataToCopy as $field)
                {
                    if (!is_null($fromCategory->getData($field)))
                    {
                        $toCategory->setData($field, $fromCategory->getData($field));
                    }
                }
                $this->searchAndReplace($toCategory);
                $toCategory->save();
            }
        }
        
    }
    
    public function searchAndReplace($category)
    {
        $fieldsToReplaceIn = array(
            'name',
            'description',
            'meta_title',
            'meta_keywords',
            'meta_description',
        );
        $search = Mage::app()->getRequest()->getParam('search');
        $replace = Mage::app()->getRequest()->getParam('replace');
        if ($search && $replace)
        {
            foreach ($fieldsToReplaceIn as $field)
            {
                if (!is_null($category->getData($field)))
                {
                    $value = $category->getData($field);
                    if ($value)
                    {
                        foreach ($search as $i => $searchEntity)
                        {
                            if ($searchEntity && isset($replace[$i]))
                            {
                                $value = str_replace($searchEntity, $replace[$i], $value);
                            }
                        }
                        $category->setData($field, $value);
                    }
                }
            }
        }
    }
    
    public function handleUrlRewrites($category)
    {
        $connection = Mage::getSingleton('core/resource') ->getConnection('core_write');
        $sql = 'DELETE FROM `' . $category->getResource()->getTable('core/url_rewrite') . '` WHERE category_id = "' . $category->getId() . '"';
        $connection->query($sql);
        Mage::getModel('catalog/url')->refreshCategoryRewrite($category->getId());
    }
}