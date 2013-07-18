<?php
class Dredd_Comparer_Block_Adminhtml_Comparer_Comparer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected $_blockGroup = 'comparer';

    public function __construct()
    {
        $this->_addButtonLabel = Mage::helper('comparer')->__('Add New Comparer');
		$this->_controller = 'adminhtml_comparer';
        $this->_headerText = Mage::helper('comparer')->__('Comparer');
		parent::__construct();
		$this->setTemplate('comparer/express.phtml');
    }

	public function getRequestUrl()
	{
		return $this->getUrl('*/*/request', array('_current'=>true, '_secure'=>true));
	}

}