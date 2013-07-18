<?php
class Dredd_Comparer_Model_Mysql4_Plan extends Mage_Core_Model_Mysql4_Abstract
{
	const CRON_STRING_PATH  = 'crontab/jobs/cron_comparer/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/cron_comparer/run/model';

	protected function _construct()
    {
		$this->_init('comparer/plan', 'comparer_plan_id');
    }

	public function getOneId()
	{
		return $this->_getReadAdapter()->fetchOne('SELECT comparer_plan_id FROM '.$this->getMainTable().' WHERE 1');
	}

	public function _afterSave(Mage_Core_Model_Abstract $object)
    {
		$frequency = $object->getData('frequency');
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

		//create config dynamically
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();

            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue('comparer/observer::generateComparer')
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        }
        catch (Exception $e) {
			Mage::throwException($e->getMessage());
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save Cron expression'));
        }
	}
}