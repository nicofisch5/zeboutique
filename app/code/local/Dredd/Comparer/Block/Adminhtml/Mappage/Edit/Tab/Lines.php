<?php
class Dredd_Comparer_Block_Adminhtml_Mappage_Edit_Tab_Lines extends Mage_Adminhtml_Block_Widget
{
	protected $_usuels = array();
	protected $_lines = array();

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('comparer/mappage.phtml');
		$this->_usuels = array(
			Dredd_Comparer_Model_Mappage::C_NONE,
			Dredd_Comparer_Model_Mappage::C_IMAGE_URL,
			Dredd_Comparer_Model_Mappage::C_PRODUCT_URL,
			Dredd_Comparer_Model_Mappage::C_CATEGORY,
			Dredd_Comparer_Model_Mappage::C_PRODUCT_ID,
			Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE,
			Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE_BARRE,
			Dredd_Comparer_Model_Mappage::C_PRODUCT_PRICE_PROMO,
			Dredd_Comparer_Model_Mappage::C_STOCK_QTY,
			Dredd_Comparer_Model_Mappage::C_CURRENCY
		);

		$this->_lines = Mage::getModel('comparer/mappageline')->getCollection()
				->addMappageFilter(Mage::registry('mappage')->getId())
				->setOrder('sort_order', 'ASC');

		$nb_comp = 0;
		if(Mage::registry('mappage')->getId()){
			$collection = Mage::getModel('comparer/comparer')->getCollection()->addMappageFilter(Mage::registry('mappage')->getId());
			$nb_comp = $collection->getSize();
		}
		$this->assign('nb_comp', $nb_comp);
	}

	public function getLines()
	{
		return $this->_lines;
	}

	protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__(''),
                    'onclick'   => 'ligne.del(this)',
                    'class' => 'delete f-right'
                ))
        );

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('comparer')->__('New Line'),
                    'onclick'   => 'ligne.add(this)',
                    'class' => 'add'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

	public function getOptionsAttributes($selected = 0, $b_slashes = false)
	{
		$t_attribute_default = array(0);

		$defaultSetId = Mage::getModel('eav/entity_type')
			->load(Mage::getModel('catalog/product')->getResource()->getTypeId())
			->getDefaultAttributeSetId();

		$groups = Mage::getModel('eav/entity_attribute_group')
                    ->getResourceCollection()
                    ->setAttributeSetFilter($defaultSetId)
                    ->load();

		$html = '';

		$str_selected = '';
		//Attribute par d�faut
		$html .= '<optgroup label="USUEL">';
		foreach($this->_usuels as $usuel){
			if ($usuel == $selected && !$str_selected) $str_selected = 'selected';
			$html .= '<option value="'.$usuel.'" '.$str_selected.'>'.$usuel;
			if ($str_selected) $str_selected = '1';
		}
		$html .= '</optgroup>';

		foreach($groups as $grp){
			$attribute_group_name = $grp->getData('attribute_group_name');
			if($b_slashes) $attribute_group_name = addslashes($attribute_group_name);
			$html .= '<optgroup label="'. $attribute_group_name .'">';

			$attributes = Mage::getModel('eav/entity_attribute')
						->getResourceCollection()
						->setAttributeGroupFilter($grp->getId())
						->load();
			foreach($attributes as $attribute) {
			
				$t_attribute_default[] = $attribute->getId();
			
			
				if ($attribute->getAttributeCode() == $selected && !$str_selected) $str_selected = 'selected';
				$html .= '<option value="'.$attribute->getAttributeCode().'" '.$str_selected.'>'.$attribute->getAttributeCode();
				if ($str_selected) $str_selected = '1';
			}

			$html .= '</optgroup>';
		}
		
		
		//other attribute set exclude default
		$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
			->addFieldToFilter('attribute_set_id', array('neq'=>$defaultSetId));
		foreach($collection as $set){
		$groups = Mage::getModel('eav/entity_attribute_group')
                   ->getResourceCollection()
                   ->setAttributeSetFilter($set->getId())
                   ->load();
			$html .= '<optgroup label="Others">';

			foreach($groups as $grp){
				$attributes = Mage::getModel('eav/entity_attribute')
								->getResourceCollection()
								->setAttributeGroupFilter($grp->getId())
								->addFieldToFilter('main_table.attribute_id', array('nin'=>$t_attribute_default))
								->load();
				foreach($attributes as $attribute) {
					if ($attribute->getAttributeCode() == $selected && !$str_selected) $str_selected = 'selected';
					$html .= '<option value="'.$attribute->getAttributeCode().'" '.$str_selected.'>'.$attribute->getAttributeCode();
					if ($str_selected) $str_selected = '1';
				}
			}
			$html .= '</optgroup>';
		}
		
		return $html;
	}
}