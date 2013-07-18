<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Block_Adminhtml_Comparer_Filename extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
		return '<a href="'.$this->getUrl('*/*/csv', array('file'=>base64_encode($value), '_secure'=>true)).'">'.$value.'</a>';
    }
}