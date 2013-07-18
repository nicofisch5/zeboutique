<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Mappage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'comparer';

	public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_mappage';

	$this->_updateButton('save', 'label', Mage::helper('comparer')->__('Save format'));
        $this->_updateButton('delete', 'label', Mage::helper('comparer')->__('Delete format'));

	$this->setValidationUrl($this->getUrl('*/*/validate', array('id' => $this->getRequest()->getParam($this->_objectId))));

		if( Mage::registry('mappage') && Mage::registry('mappage')->getId() ) {
			$model = Mage::registry('mappage');
			$collection = Mage::getModel('comparer/comparer')->getCollection()->addMappageFilter($model->getId());
			if($collection->getSize()) {
				$this->_updateButton('delete', 'id', 'btnDelete');
				$this->_updateButton('delete', 'onclick', "confirmComparer();");

				$data = array(
					'id' => 'delChild',
					'name' => 'delChild',
					'label' => 'Remove ALL exports using this format',
					'class'	=> 'disabled',
					'onclick' => "deleteComparer(".$model->getId().", '".$this->getUrl('*/*/delChild', array('_current'=>true))."', '".$this->getUrl('*/*/delete', array('_current'=>true))."');",
					'disabled'	=> 'true',
				);
				$this->_addButton('delChild', $data);
			}
		}
    }

	public function getHeaderText()
    {
	if( Mage::registry('mappage') && Mage::registry('mappage')->getId() ) {
            return Mage::helper('comparer')->__("Edit Format '%s'", $this->htmlEscape(Mage::registry('mappage')->getComparerMappageName()));
        } else {
            return Mage::helper('comparer')->__('New Format');
        }
    }
}