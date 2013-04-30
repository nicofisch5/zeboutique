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
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product search result block
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Zeboutique_CatalogSearch_Block_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
        }

        // Add Zeb filter
        $catFilter = $this->_getStoreCategoryFilter();
        $this->_productCollection->joinField(
            'zecp',
            'catalog/category_product',
            '',
            'product_id=entity_id',
            'at_zecp.category_id IN ('.$catFilter.')',
            'inner'
        );
        $this->_productCollection->distinct(true);

        return $this->_productCollection;
    }

    /**
     * Get store category filter
     *
     * @return string
     */
    private function _getStoreCategoryFilter()
    {
        $rootcatId = Mage::app()->getStore()->getRootCategoryId();
        $catColl = Mage::getModel('catalog/category')->getCollection()
            ->addFieldToFilter('path', array('like'=>'%/'.$rootcatId.'/%'))
            ->addAttributeToFilter('is_active', 1);

        if ($catColl->getSize()) {
            return implode(',', $catColl->getAllIds());
        } else {
            return '';
        }
    }
}
