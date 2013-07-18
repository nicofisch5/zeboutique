<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Mappage_Mappage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected $_blockGroup = 'comparer';

    public function __construct()
    {
        $this->_controller = 'adminhtml_mappage';
        $this->_headerText = Mage::helper('comparer')->__('Format');
        $this->_addButtonLabel = Mage::helper('comparer')->__('Add new format');
        parent::__construct();
    }

}