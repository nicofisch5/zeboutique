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
 * @package     Zeboutique_Comparer
 * @copyright   Copyright (c) 2014 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Comparer
 *
 * @category    Zeboutique
 * @package     Zeboutique_Comparer
 * @author      Zeboutique
 */
class Zeboutique_Comparer_Block_Adminhtml_Comparer_Edit_Product extends Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Product
{
    protected function _prepareCollection()
    {
        if ($this->getComparer()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addStoreFilter($this->_getStore());

        $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $this->_getStore());
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $this->_getStore());
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $this->_getStore());

        $collection->joinAttribute('manufacturer', 'catalog_product/manufacturer', 'entity_id', null, 'left', $this->_getStore());

        //filtre sur stock
        $stock = Dredd_Comparer_Model_Comparer::C_STOCK;
        if ($this->getComparer()->getId()) {
            $stock = $this->getComparer()->getInStock();
        }
        if($this->getRequest()->getParam('stock')) $stock = $this->getRequest()->getParam('stock');

        $critere = " And {{table}}.is_in_stock = '".$stock."'";

        if($stock==Dredd_Comparer_Model_Comparer::C_STOCK_ALL) $critere = "";

        $collection->joinField('is_in_stock',
            'cataloginventory/stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '{{table}}.stock_id=1 '.$critere,
            'inner');

        if($this->getRequest()->getParam('catIds', false)) {
            $this->setDefaultFilter(null);
        }

        //filtre sur cat�gorie
        $catIds = 0;
        if ($this->getComparer()->getId()) {
            $catIds = $this->getComparer()->getCategoryIds();
        }
        //if($this->getRequest()->getParam('catIds', false)) $catIds = $this->getRequest()->getParam('catIds', 0);
        if($this->getRequest()->getParam('catIds'))
            $catIds = $this->getRequest()->getParam('catIds', 0);
        $collection
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id IN ('.$catIds.')',
                'inner');

        unset ($catIds);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        //Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        $collection->addFieldToFilter('visibility', array('neq'=>1));
        //getSelect()->where("visibility <> ");

        $collection->setOrder('custom_name', 'ASC');
        $collection->getSelect()->distinct(true);

        //Modifier l'Unique id de chaque ligne
        $collection->setRowIdFieldName(uniqid());
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        // Manufacturer
        $this->addColumnAfter(
            'manufacturer',
            array(
                'header'=> Mage::helper('catalog')->__('Manufacturer'),
                'index' => 'manufacturer',
                'type'  => 'options',
                'options' => $this->_getColumnOptions('manufacturer'),
            ),
            'custom_name'
        );

        $this->sortColumnsByOrder();

        return $this;
    }

    /**
     * Get options for an attribute
     *
     * @param string $attCode
     * @return array
     */
    protected function _getColumnOptions($attCode)
    {
        $attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attCode);
        $options = array();

        if ($attr->getFrontendInput() == 'select') {
            $values = Mage::getResourceModel( 'eav/entity_attribute_option_collection' )->setAttributeFilter(
                $attr->getId() )
                ->setStoreFilter( $this->_getStore(), false)
                ->load();

            $options = array();
            foreach ($values as $value) {
                $options[$value->getOptionId()] = $value->getValue();
            }
        }

        return $options;
    }
}