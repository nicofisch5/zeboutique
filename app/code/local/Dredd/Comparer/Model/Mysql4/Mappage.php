<?php
class Dredd_Comparer_Model_Mysql4_Mappage extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
		$this->_init('comparer/mappage', 'comparer_mappage_id');
    }

	public function _afterSave(Mage_Core_Model_Abstract $object)
    {
		foreach($object->getLines() as $obj){
			$obj->setComparerMappageId($object->getId());
			$obj->save();
		}
	}
}