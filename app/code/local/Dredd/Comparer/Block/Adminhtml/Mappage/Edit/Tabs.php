<?php
class Dredd_Comparer_Block_Adminhtml_Mappage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mappage_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('comparer')->__('Information'));
    }

	protected function _prepareLayout()
    {
	$this->addTab('form_section', array(
            'label'     => Mage::helper('comparer')->__('Information'),
            'title'     => Mage::helper('comparer')->__('Information'),
            'content'   => $this->getLayout()->createBlock('comparer/adminhtml_mappage_edit_tab_form')->toHtml(),
        ));

	$this->addTab('mappage_section', array(
            'label'     => Mage::helper('comparer')->__('Mappage'),
            'title'     => Mage::helper('comparer')->__('Mappage'),
            'content'   => $this->getLayout()->createBlock('comparer/adminhtml_mappage_edit_tab_lines')->toHtml(),
        ));
	return parent::_prepareLayout();
	}
}