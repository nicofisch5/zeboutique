<?php
class Dredd_Comparer_Model_Mysql4_Plan_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('comparer/plan');
    }

	public function toOptionArray()
    {
        return $this->_toOptionArray('comparer_plan_id', 'email');
    }
	public function toOptionHash()
    {
        return parent::_toOptionHash('comparer_plan_id', 'email');
    }
}