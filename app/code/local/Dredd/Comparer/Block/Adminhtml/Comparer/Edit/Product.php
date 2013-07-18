<?php
class Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Product extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('comparer_category_products');
        $this->setDefaultSort('name', 'asc');
        $this->setUseAjax(true);
    }

public function getComparer()
    {
        return Mage::registry('comparer_data');
    }

	protected function _getStore()
	{
		return $this->getComparer()->getStoreId();
	}

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

		return parent::_prepareCollection();
	}

	protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

	protected function _prepareColumns()
    {
        /*$this->addColumn('in_category', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_category',
			'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id',
			'renderer'	=> 'comparer/adminhtml_comparer_edit_renderer_checkbox',
        ));*/
        $this->addColumn('id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('custom_name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'custom_name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));
		$this->addColumn('is_in_stock', array(
            'header'    => Mage::helper('catalog')->__('In Stock'),
            'width'     => '80px',
            'index'     => 'is_in_stock',
			'align'		=> 'center',
			'filter'	=> false,
			'sortable'	=> false,
			'renderer'	=> 'comparer/adminhtml_comparer_edit_renderer_stock',
        ));

        //$this->setFilterVisibility(false);

		return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> 'No action',
             'url'  => "javascript:alert('" . Mage::helper('comparer')->__('Please, use buttons above') . "');",
        ));

		return $this;
	}

	protected function _getSelectedProducts()
	{
		
		$t_prod = array('0');
		if ($this->getComparer()->getId()) {
			$t_prod = explode(',', $this->getComparer()->getProductIds());
		}

		if($productIds = $this->getRequest()->getParam('productIds')) {
			$t_productIds = explode(',', $productIds);
			$t_prod = array_merge($t_prod, $t_productIds);
		}
		
		return $t_prod;
	}

	public function getGridUrl()
    {
        return $this->getUrl('*/*/product', array('_current'=>true, '_secure'=>true));
    }

	public function getRowUrl($row)
    {
        return '';
    }

}