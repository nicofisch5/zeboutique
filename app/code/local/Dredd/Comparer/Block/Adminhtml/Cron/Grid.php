<?php
/**
* @package Dredd_Comparer
* @author Haingo
* @email haingo@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Cron_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
    {
        parent::__construct();
        $this->setId('cronGrid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
		$this->setUseAjax(true);
    }

	protected function _prepareCollection()
    {
	 $collection = Mage::getModel('comparer/cron')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();

		return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('comparer_id', array(
            'header'    => Mage::helper('comparer')->__('Comparers'),
            'index'     => 'comparer_id',
            'type'		=> 'options',
            'options'	=> Mage::getModel('comparer/comparer')->getCollection()->setOrder('name', 'ASC')->toOptionHash(),
        ));

		$this->addColumn('timing', array(
            'header'    => Mage::helper('comparer')->__('Timing'),
            'align'     =>'left',
			'type'		=>'time',
            'index'     => 'timing',
        ));
		$this->addColumn('frequency', array(
            'header'    => Mage::helper('comparer')->__('Frequency'),
            'align'     =>'left',
			'index'     => 'frequency',
			'type'		=> 'options',
			'options'	=> Mage::helper('comparer/cron')->toOptionHashFrequency(),
        ));
		$this->addColumn('email', array(
            'header'    => Mage::helper('comparer')->__('E-mail'),
            'align'     =>'left',
			'type'		=>'text',
            'index'     => 'email',
        ));
		$this->addColumn('status', array(
            'header'     => Mage::helper('comparer')->__('Status'),
            'index'     => 'status',
            'type'		=> 'options',
			'options'    => array(
                '1' => Mage::helper('comparer')->__('Active'),
                '0' => Mage::helper('comparer')->__('Inactive'),
            ),
        ));



		 $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('comparer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('comparer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));


		return parent::_prepareColumns();
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