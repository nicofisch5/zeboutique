<?php
class Dredd_Comparer_Model_Mysql4_Croning extends Mage_Core_Model_Mysql4_Abstract
{
    const CRON_STRING_PATH  = 'crontab/jobs/cron_comparer{{id}}/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/cron_comparer{{id}}/run/model';

	protected function _construct()
    {
		$this->_init('comparer/croning', 'comparer_cron_id');
		//$this->_uniqueFields = array( array('field' => 'comparer_id', 'title' => Mage::helper('comparer')->__('Cron planning for this comparer') ) );
    }

	public function getIdByComparerId($comparer_id)
	{
		return $this->_getReadAdapter()->fetchOne('SELECT comparer_cron_id from '.$this->getMainTable().' WHERE comparer_id=?', $comparer_id);
	}

	public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
		/*$frequency = $object->getData('frequency');
		$start_time = $object->getData('start_time');
		$time = explode(':', $start_time);

		$frequencyDaily     = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly    = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

		$cronDayOfWeek = date('N');
        $cronExprArray = array(
            intval($time[1]),                                   # Minute
            intval($time[0]),                                   # Hour
            ($frequency == $frequencyMonthly) ? '1' : '*',      # Day of the Month
            '*',                                                # Month of the Year
            ($frequency == $frequencyWeekly) ? '1' : '*',       # Day of the Week
        );
        $cronExprString = join(' ', $cronExprArray);

		//Mage::throwException($cronExprString);
		$object->setData('cron_string', $cronExprString);*/
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