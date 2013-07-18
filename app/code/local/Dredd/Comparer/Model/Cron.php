<?php
class Dredd_Comparer_Model_Cron extends Mage_Core_Model_Abstract
{
	protected function _construct()
    {
        $this->_init('comparer/cron', true);
    }

	public function canDelete()
	{
		return true;
	}
}