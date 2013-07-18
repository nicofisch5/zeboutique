<?php
class Dredd_Comparer_Block_Adminhtml_Comparer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
		$nombre = 0;
		if($ids = $row->getData('product_ids')){
			$nombre = count( explode(',', $ids) );
		}
		if($nombre <=0) return '';
		return '<a href="javascript:{}" onclick="loadValues('.$row->getData('comparer_id').', \''.  $this->getUrl('*/*/load', array('_current'=>true, '_secure'=>true))   .'\'); return false;">'.  Mage::helper('comparer')->__('Regenerate Csv') . '('. $nombre .')' .'</a>';
    }

}