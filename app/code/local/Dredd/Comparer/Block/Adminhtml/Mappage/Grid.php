<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Mappage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
    {
        parent::__construct();
        $this->setId('mappageGrid');
        $this->setDefaultSort('comparer_mappage_name');
        $this->setDefaultDir('ASC');
	$this->setUseAjax(true);
    }

	protected function _prepareCollection()
    {
	$collection = Mage::getModel('comparer/mappage')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('comparer_mappage_id', array(
            'header'    => Mage::helper('comparer')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'comparer_mappage_id',
        ));

	$this->addColumn('comparer_mappage_name', array(
            'header'    => Mage::helper('comparer')->__('Name'),
            'align'     =>'left',
            'index'     => 'comparer_mappage_name',
        ));

	$this->addColumn('action',
            array(
                'header'    => Mage::helper('comparer')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',	'align'	=> 'center',
                'actions'   => array(
                                 array(
                                   'caption' => Mage::helper('comparer')->__('Edit'),
                                   'url'     => array(
                                   'base'=>'*/*/edit',
                                   'params'=>array('comparer_id'=>$this->getRequest()->getParam('comparer_id'))
                                 ),
                            'field' => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
