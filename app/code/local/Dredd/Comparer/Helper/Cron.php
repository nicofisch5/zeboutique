<?php
class Dredd_Comparer_Helper_Cron extends Mage_Core_Helper_Abstract
{
	public function toOptionHashFrequency(){
		$frequencyOprions = array();
		$frequency = Mage::getModel('adminhtml/system_config_source_cron_frequency')->toOptionArray();
		foreach($frequency  as $value){
			$frequencyOprions[$value['value']] = $value['label'];
		}
		return $frequencyOprions;
	}
}