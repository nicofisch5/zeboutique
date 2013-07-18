<?php
class Dredd_Comparer_Block_Adminhtml_Mappage_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('comparer')->__('Format General Information')));

		$fieldset->addField('comparer_mappage_name', 'text', array(
            'name'      => 'comparer_mappage_name',
            'label'     => Mage::helper('comparer')->__('Name'),
            'title'     => Mage::helper('comparer')->__('Name'),
            'required'  => true,
        ));

		$fieldset->addField('comparer_mappage_header', 'textarea', array(
            'name'      => 'comparer_mappage_header',
            'label'     => Mage::helper('comparer')->__('Header'),
            'title'     => Mage::helper('comparer')->__('Header'),
            'required'  => false,
			'note'		=> 'ex, kelkoo use #type=basic',
        ));

		$fieldset->addField('comparer_mappage_separator', 'text', array(
            'name'      => 'comparer_mappage_separator',
            'label'     => Mage::helper('comparer')->__('Separator'),
            'title'     => Mage::helper('comparer')->__('Separator'),
            'required'  => true,
			'note'		=> '<strong>Use \t for tabulation</strong>',
        ));

		/*$fieldset->addField('comparer_mappage_tag', 'text', array(
            'name'      => 'comparer_mappage_tag',
            'label'     => Mage::helper('comparer')->__('Tag Tracking'),
            'title'     => Mage::helper('comparer')->__('Tag Tracking'),
            'required'  => false,
			'note'		=> '<strong>ex, var1=azerty&var2=123456&var3=pdt_sku_and_name</strong>',
        ));

		$texte = Mage::helper('comparer')->__('Enter your tracking url, which will add to the url of the product.<br>You can pass in the URL tracking :<br>- SKU and the product name: For this, use the url &laquo;pdt_sku_and_name&raquo;<br>- The product ID and product name: For this, use the url &laquo;pdt_id_and_name&raquo;<br>to be replaced by Product info');
		$texte = '<div style="width:480px; border-top:1px solid #e1e6f2;"><small>'.$texte.'</small></div>';
		$fieldset->addField('code_tracking_libelle', 'note', array(
            'name'      => 'code_tracking_libelle',
            'text'		=> $texte,
        ));*/

        $form->setValues(Mage::registry('mappage_data')->getData());

		$this->setForm($form);
		return parent::_prepareForm();
	}
	
}