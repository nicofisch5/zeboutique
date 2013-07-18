<?php
class Dredd_Comparer_Model_Mysql4_Comparer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('comparer/comparer');
    }

	public function addMappageFilter($comparer_mappage_id)
	{
		$this->_select->where("comparer_mappage_id IN (?)", $comparer_mappage_id);
		return $this;
	}

	public function joincron()
	{
		$this->_select->joinLeft(
            array('cr' => $this->getTable('comparer/cron')),
            'cr.comparer_id=main_table.comparer_id',
            array('status', 'comparer_cron_id', 'timing', 'frequency')
        );
		return $this;
	}

	public function toOptionArray()
    {
        return $this->_toOptionArray('comparer_id', 'name');
    }
	public function toOptionHash()
    {
        return parent::_toOptionHash('comparer_id', 'name');
    }
}