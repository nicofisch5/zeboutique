<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
* @licence Commercial
*/
class Dredd_Comparer_Block_Adminhtml_Comparer_New extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout()
    {
		$this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "if($('name').value=='') {alert('".Mage::helper('comparer')->__('Please fill name')."'); $('name').focus(); return false;} if(document.getElementById('store_id').options[document.getElementById('store_id').selectedIndex].value==0) {alert('".Mage::helper('comparer')->__("Please choose a Store View")."'); return false; } setLocation('".$this->getContinueUrl()."type/'+document.getElementById('type_id').options[document.getElementById('type_id').selectedIndex].value+'/store/'+document.getElementById('store_id').options[document.getElementById('store_id').selectedIndex].value+'/name/'+ $('name').value.replace(/ /g, '-') +'/');",
                    'class'     => 'save'
                    ))
                );
		return parent::_prepareLayout();
    }

	protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('settings', array('legend'=>Mage::helper('comparer')->__('Export Settings')));
				$fieldset->addField('type_id', 'select', array(
            'name'      => 'type_id',
            'label'     => Mage::helper('comparer')->__('Type'),
            'title'     => Mage::helper('comparer')->__('Type'),
            'required'  => true,
            'values' 	=> Mage::getModel('comparer/mappage')->getCollection()->setOrder('comparer_mappage_name')->toOptionArray(),
        ));

	$fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('comparer')->__('Store View'),
            'title'     => Mage::helper('comparer')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
        ));

	$fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('comparer')->__('Name'),
            'title'     => Mage::helper('comparer')->__('Name'),
            'required'  => true,
        ));

	$fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $this->setForm($form);
    }

    public function getContinueUrl()
    {
        return $this->getUrl('*/*/edit', array('_current'=>true, '_secure'=>true));
    }
}