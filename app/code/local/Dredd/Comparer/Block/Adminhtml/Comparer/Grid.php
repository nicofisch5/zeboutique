<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Comparer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
    {
        parent::__construct();
        $this->setId('comparerGrid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
	$this->setUseAjax(true);
    }

	protected function _prepareCollection()
    {
	$collection = Mage::getModel('comparer/comparer')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();

		return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('comparer_mappage_id', array(
            'header'    => Mage::helper('comparer')->__('Format'),
            'index'     => 'comparer_mappage_id',
            'type'		=> 'options',
            'options'	=> Mage::getModel('comparer/mappage')->getCollection()->setOrder('comparer_mappage_name', 'ASC')->toOptionHash(),
        ));

	$this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

	$this->addColumn('updated_at', array(
            'header'    => Mage::helper('comparer')->__('Updated At'),
            'align'     =>'left',
            'index'     => 'updated_at',
        ));

	$this->addColumn('filename', array(
            'header'    => Mage::helper('comparer')->__('Csv'),
            'align'     =>'left',
            'index'     => 'filename',
			'renderer'	=> 'comparer/adminhtml_comparer_filename',
        ));

		/**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('comparer')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

		$this->addColumn('action',
            array(
                'header'    => Mage::helper('comparer')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
				'align'		=> 'center',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('comparer')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('comparer_id'=>$this->getRequest()->getParam('comparer_id'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

		$this->addColumn('express', array(
            'header'    => Mage::helper('comparer')->__('Action'),
            'align'     =>'center',
            'index'     => 'comparer_id',
			'filter'    => false,
            'sortable'  => false,
			'renderer'	=> 'comparer/adminhtml_comparer_action',
        ));

		return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('comparer_id');
        $this->getMassactionBlock()->setFormFieldName('comparer');

        $statuses = array(
			Dredd_Comparer_Model_Comparer::C_STOCK => Mage::helper('comparer')->__('All'),
			Dredd_Comparer_Model_Comparer::C_STOCK_ALL => Mage::helper('comparer')->__('In stock'),
			Dredd_Comparer_Model_Comparer::EXPORT_STOCK_STOCKNOTMANAGED => Mage::helper('comparer')->__('In stock & stock not managed'),
		);

        array_unshift($statuses, array('label'=>'', 'value'=>''));
/*
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('comparer')->__('Change export parameter'),
             'url'  => $this->getUrl('massExportParameter', array('_current'=>true, '_secure'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('comparer')->__('Export parameter'),
                         'values' => $statuses
                     )
             )
        ));
*/
	$this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete', array('_current'=>true, 'secure'=>true)),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        return $this;
    }

	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true, '_secure'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), '_secure'=>true));
    }

}