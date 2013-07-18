<?php
class Dredd_Comparer_Model_Mysql4_Mappageline_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('comparer/mappageline');
    }

	public function addMappageFilter($comparer_mappage_id)
	{
		$this->_select->where("comparer_mappage_id IN (?)", $comparer_mappage_id);
		return $this;
	}
}