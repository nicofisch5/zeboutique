<?php
class Dredd_Comparer_Model_Mysql4_Mappageline extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
		$this->_init('comparer/mappageline', 'comparer_mappage_line_id');
    }
}