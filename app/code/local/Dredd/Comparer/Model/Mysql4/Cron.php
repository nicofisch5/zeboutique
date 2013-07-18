<?php
class Dredd_Comparer_Model_Mysql4_Cron extends Mage_Core_Model_Mysql4_Abstract
{
    const CRON_STRING_PATH  = 'crontab/jobs/cron_comparer{{id}}/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/cron_comparer{{id}}/run/model';

	protected function _construct()
    {
		$this->_init('comparer/cron', 'comparer_cron_id');
		//$this->_uniqueFields = array( array('field' => 'comparer_id', 'title' => Mage::helper('comparer')->__('Cron planning for this comparer') ) );
    }

	public function getIdByComparerId($comparer_id)
	{
		return $this->_getReadAdapter()->fetchOne('SELECT comparer_cron_id from '.$this->getMainTable().' WHERE comparer_id=?', $comparer_id);
	}

	public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
		$timing = $object->getData('timing');
		if(is_array($timing))$timing = implode(':', $timing);
		$object->setData('timing', $timing);
		if(!$object->getData('id')){
			$object->setData('created_at', date('Y-m-d H:m:s', Mage::getModel('core/date')->timestamp(time())));
		}
	}

	protected function _replaceId($str, $id)
	{
		return str_replace('{{id}}', $id, $str);
	}

	public function _afterSave(Mage_Core_Model_Abstract $object)
    {
		$cronExprString = $object->getData('cron_string');

		//create config dynamically
        /*try {
            Mage::getModel('core/config_data')
                ->load($this->_replaceId(self::CRON_STRING_PATH, $object->getId()), 'path')
                ->setValue($cronExprString)
                ->setPath($this->_replaceId(self::CRON_STRING_PATH, $object->getId()))
                ->save();

            Mage::getModel('core/config_data')
                ->load($this->_replaceId(self::CRON_MODEL_PATH, $object->getId()), 'path')
                ->setValue('comparer/cron::generateComparer')
                ->setPath($this->_replaceId(self::CRON_MODEL_PATH, $object->getId()))
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save Cron expression'));
        }*/
	}
}