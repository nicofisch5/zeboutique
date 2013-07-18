<?php
class Dredd_Comparer_Model_Mysql4_Mappage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('comparer/mappage');
    }

	public function toOptionArray()
    {
        return $this->_toOptionArray('comparer_mappage_id', 'comparer_mappage_name');
    }
	public function toOptionHash()
    {
        return parent::_toOptionHash('comparer_mappage_id', 'comparer_mappage_name');
    }
}