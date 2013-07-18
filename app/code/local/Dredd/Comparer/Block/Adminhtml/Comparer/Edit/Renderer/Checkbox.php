<?php

class Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Renderer_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
	/**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
		$value = $row->getData($this->getColumn()->getIndex());

		$checked = (in_array($value, $this->getValues())) ? "checked" : "";
		return '<input type="checkbox" name="categories[]" value="'.$value.'" '.$checked.' onclick="checkProduct(this);" />';
    }
}