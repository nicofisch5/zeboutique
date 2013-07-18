<?php
class Dredd_Comparer_Model_Mysql4_Comparer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
		$this->_init('comparer/comparer', 'comparer_id');
    }

	protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
		Mage::helper('comparer')->generateCsv($object);
	}

	protected function _beforeDelete(Mage_Core_Model_Abstract $object)
	{
		$filename = $object->getFilename();
		$file = BP . DS . 'comparateur' . DS . $filename;
		if(file_exists($file) && $file!='.' && $file!='..') {
			@unlink($file);
		}
	}
}