<?php
/**
* @package Dredd_Comparer
* @author Haingo
* @email haingo@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Cron_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'comparer';

	public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_cron';

		$this->_updateButton('save', 'label', Mage::helper('comparer')->__('Save Cron'));
		$this->_updateButton('update', 'label', Mage::helper('comparer')->__('Update Cron'));
   	 	$this->_updateButton('delete', 'label', Mage::helper('comparer')->__('Delete Cron'));
    }

	public function getHeaderText()
    {
		if( Mage::registry('cron_data') && Mage::registry('cron_data')->getId() ) {
            return Mage::helper('comparer')->__('Edit Cron');
        } else {
            return Mage::helper('comparer')->__('New Cron');
        }
    }
}