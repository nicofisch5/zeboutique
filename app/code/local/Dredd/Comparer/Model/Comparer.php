<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Model_Comparer extends Mage_Core_Model_Abstract
{
	const C_STOCK = 1;
	const C_STOCK_ALL = 2;
	
	CONST EXPORT_ALL = 1;
	CONST EXPORT_STOCK = 2;
	CONST EXPORT_STOCK_STOCKNOTMANAGED = 3;
	
	protected function _construct()
    {
        $this->_init('comparer/comparer', true);
    }

	public function generateComparer($observer)
	{
		foreach($this->getCollection() as $comparer){
			$config = Mage::getStoreConfig('crontab/jobs/cron_comparer'.$comparer->getId().'/schedule/cron_expr');
			if (!$config) continue;
			//generate cron
			file_put_contents('C:/wamp/www/magento3/var/test'.$comparer->getId().'.txt', $comparer->getId());
		}
	}

	public function canDelete()
	{
		return true;
	}
}