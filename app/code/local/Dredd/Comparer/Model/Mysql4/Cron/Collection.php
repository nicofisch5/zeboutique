<?php
class Dredd_Comparer_Model_Mysql4_Cron_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('comparer/cron');
    }

	public function addComparerFilter($comparer_id)
	{
		$this->_select->where("comparer_id IN (?)", $comparer_id);
		return $this;
	}

	public function addEnabledFilter()
	{
		$this->_select->where("status = 1");
		return $this;
	}

	public function joinComparer()
	{
		$this->_select->join(
            array('cp' => $this->getTable('comparer/comparer')),
            'cp.comparer_id=main_table.comparer_id',
            array('comparer_id', 'name', 'comparer_mappage_id', 'updated_at', 'category_ids', 'in_stock', 'store_id', 'product_ids', 'tracking', 'filename')
        );
		return $this;
	}
}