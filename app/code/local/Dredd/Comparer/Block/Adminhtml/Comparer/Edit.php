<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Comparer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'comparer';

	public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_comparer';

	$this->_updateButton('save', 'label', Mage::helper('comparer')->__('Save Comparer'));
//	$this->_updateButton('save', 'onclick', "verifVide(); initPostValues(); chrono(); postValues();");
	$this->_updateButton('save', 'onclick', "verifVide();");
        $this->_updateButton('delete', 'label', Mage::helper('comparer')->__('Delete Comparer'));

	$this->setValidationUrl($this->getUrl('*/*/validate', array('id' => $this->getRequest()->getParam($this->_objectId), '_secure'=>true)));

    }

	public function getHeaderText()
    {
		if( Mage::registry('comparer') && Mage::registry('comparer')->getId() ) {
            return Mage::helper('comparer')->__("Edit Comparer '%s'", $this->htmlEscape(Mage::registry('comparer')->getName()));
        } else {
            return Mage::helper('comparer')->__('New Comparer');
        }
    }
}