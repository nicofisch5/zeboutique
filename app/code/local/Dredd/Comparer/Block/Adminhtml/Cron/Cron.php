<?php
class Dredd_Comparer_Block_Adminhtml_Cron_Cron extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected $_blockGroup = 'comparer';

    public function __construct()
    {
        $this->_addButtonLabel = Mage::helper('comparer')->__('Add New Cron');
		$this->_controller = 'adminhtml_cron';
        $this->_headerText = Mage::helper('comparer')->__('Cron');
		parent::__construct();
		// $this->setTemplate('comparer/express.phtml');
    }
}