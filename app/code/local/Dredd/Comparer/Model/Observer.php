<?php
/**
 * @package Dredd_Comparer
 * @author Haingo
 * @email haingo@madadev.fr
 */
class Dredd_Comparer_Model_Observer
{
    public function generateComparer($observer)
    {
		$collection = Mage::getModel('comparer/cron')->getCollection()
        ->addEnabledFilter();
        foreach($collection as $cron){
            try{
                $comparer = Mage::getModel('comparer/comparer')->load($cron->getComparerId());
                	
                $now = $this->_mysqlDateToZendDate(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
                $hour = explode(':',$cron->getTiming());
                $email = $cron->getEmail();
                if($cron->getStatus()){
                    switch($cron->getFrequency()){
                        /**
                         * Quotidien
                         * $newExecution = date d'aujourd'hui concatener à heure timing
                         */
                        case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY :
                            // $newExecution = $this->_newZendDate($now->get(Zend_Date::YEAR),$now->get(Zend_Date::MONTH),$now->get(Zend_Date::DAY),$hour[0],$hour[1],$hour[2]);
                            $newExecution = $this->_getNewDateExecution($now,$hour);
                            $lastExecution = $this->_mysqlDateToZendDate($cron->getExecuteAt());
                            //  if((($newExecution<$now) || ($newExecution==$now)) && ($newExecution > $lastExecution))
                            if((($newExecution->compare($now)== -1) || ($newExecution->compare($now)== 0)) && ($newExecution->compare($lastExecution) ==1)){
                                $this->_regenerateCSV($comparer);
                               $cron->setExecuteAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
                                $cron->save();
                            }
                            break;
                            /**
                             * Hebdomadaire
                             * $newExecution = date created_at concatener à heure timing si $lastExecution = '0000-00-00 00:00:00'
                             * $newExecution = date execute_at concatener à heure timing si $lastExecution != '0000-00-00 00:00:00'
                             * $newExecution = $newExecution + 1semaine
                             */
                        case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY :
                            $defaultLastExecution = $this->_mysqlDateToZendDate('0000-00-00 00:00:00');
                            $lastExecution = $this->_mysqlDateToZendDate($cron->getExecuteAt());
                            if($lastExecution->compare($defaultLastExecution) == 0){ // pas encore executé
                                $createdAt = $this->_mysqlDateToZendDate($cron->getCreatedAt());
                                $newExecution = $this->_getNewDateExecution($createdAt,$hour);
                            }else{
                                $newExecution = $this->_getNewDateExecution($lastExecution,$hour);
                            }
                            $newExecution->addWeek(1);
                            //  if((($newExecution<$now) || ($newExecution==$now)) && ($newExecution > $lastExecution))
                            if((($newExecution->compare($now)== -1) || ($newExecution->compare($now)== 0)) && ($newExecution->compare($lastExecution) ==1)){
                                $this->_regenerateCSV($comparer);
                                $cron->setExecuteAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
                                $cron->save();
                            }
                            break;

                            /**
                             * Mensuel
                             * $newExecution = date created_at concatener à heure timing si $lastExecution = '0000-00-00 00:00:00'
                             * $newExecution = date execute_at concatener à heure timing si $lastExecution != '0000-00-00 00:00:00'
                             * $newExecution = $newExecution + 1mois
                             */
                        case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY :
                            $defaultLastExecution = $this->_mysqlDateToZendDate('0000-00-00 00:00:00');
                            $lastExecution = $this->_mysqlDateToZendDate($cron->getExecuteAt());
                            if($lastExecution->compare($defaultLastExecution) == 0){ // pas encore executé
                                $createdAt = $this->_mysqlDateToZendDate($cron->getCreatedAt());
                                $newExecution = $this->_getNewDateExecution($createdAt,$hour);
                            }else{
                                $newExecution = $this->_getNewDateExecution($lastExecution,$hour);
                            }
                            	
                            $newExecution->addMonth(1);
                            //  if((($newExecution<$now) || ($newExecution==$now)) && ($newExecution > $lastExecution))
                            if((($newExecution->compare($now)== -1) || ($newExecution->compare($now)== 0)) && ($newExecution->compare($lastExecution) ==1)){
                                $this->_regenerateCSV($comparer);
                                $cron->setExecuteAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
                                $cron->save();
                            }
                            break;
                    }
                }
            }catch(Exception $e){
                $this->_sendMailError($e->getMessage(),$email);
            }
        }
        return $this;
    }


    protected function _sendMailError($error_message,$email){
        $mailer = new Zend_Mail();
        $mailer->addTo($email);
        $mailer->setFrom($email);
        $mailer->setSubject('Cron Comparer Error');
        $mailer->setBodyHtml($error_message);
        $mailer->send();
    }

    protected function _newZendDate($year,$month,$day,$hour,$minute,$second){
        $dateArray = array('year'	=> $year,
 							'month'	=>$month,
							'day'	=>$day,
							'hour'	=>$hour,
							'minute'=>$minute,
							'second'=>$second
        );
        return new Zend_Date($dateArray);
    }

    /*
     * $mysqlDate de type Y-m-d H:i:s
     */
    protected function _mysqlDateToZendDate($mysqlDate){
        $mysqlDateArrayInterm = explode(' ',$mysqlDate);
        $mysqlDateArrayInterm1 = explode('-',$mysqlDateArrayInterm[0]);
        $mysqlDateArrayInterm2 = explode(':',$mysqlDateArrayInterm[1]);
        return $this->_newZendDate($mysqlDateArrayInterm1[0],$mysqlDateArrayInterm1[1],$mysqlDateArrayInterm1[2],$mysqlDateArrayInterm2[0],$mysqlDateArrayInterm2[1],$mysqlDateArrayInterm2[2]);
    }

    /*
     * $date de type Zend_Date (date d'execution)
     * $time de type h:i:s (timing)
     */
    protected function _getNewDateExecution($date,$time){
        return $this->_newZendDate($date->get(Zend_Date::YEAR),$date->get(Zend_Date::MONTH),$date->get(Zend_Date::DAY),$time[0],$time[1],$time[2]);
    }

    protected function _regenerateCSV($comparer)
    {
		$comparer->setUpdatedAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
        $comparer->setcronAt(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
        $comparer->save();

        $store_id = $comparer->getStoreId();
		$product_ids = $comparer->getProductIds();
		$t_product_ids = explode(',', $product_ids);
		$nb_product_ids = count($t_product_ids);
		$t_page = array(0,99); 
		$t_mappage = array();
		$t_ids = array();
		while($t_page[1]<($nb_product_ids+99)){
			if($nb_product_ids<=100) {
	            $t_page[0] = 0;
	            $t_page[1] = $nb_product_ids-1;
	        }
	        $t_ids = array(0);
	
	        if (isset($t_page[1]) && $t_page[1] > $nb_product_ids) {
	            $t_page[1] = $nb_product_ids-1;
	        }
	
	        if (isset($t_page[0]) && isset($t_page[1])) {
	            for($i=$t_page[0]; $i<=$t_page[1]; $i++){
	                $t_ids[] = $t_product_ids[$i];
	            }
	        }
			
			$mappage = Mage::getModel('comparer/mappage')->load($comparer->getData('comparer_mappage_id'));
	        $separator = $mappage->getComparerMappageSeparator();
	        if($separator == '\t') $separator = '	';
	        $lines = Mage::getModel('comparer/mappageline')->getCollection()
	        ->addMappageFilter($mappage->getId())
	        ->setOrder('sort_order', 'ASC');
			
	        foreach($lines as $line){
	            $attribute_code = $line->getAttributeCode();
	            if($attribute_code != "none"){
	                array_push($t_mappage, $attribute_code);
	            }
	        }
	        $csv = '';
		    $nb_ligne = count($t_ids);
			$entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
	        $entityTypeId = $entityType->getEntityTypeId();
			$csv = Mage::helper('comparer/collect')->generateLineCollect($comparer, $separator, $lines, $t_ids, $entityTypeId);
	        $filename = $comparer->getFilename();
	        $file = BP . DS . 'comparateur' . DS . $filename;
	
	        if($csv!="" && $csv!="\r\n"){
	            $objFile = new SplFileObject($file, "a");
	            $objFile->fwrite($csv);
	        }
			
			$t_page[0] = $t_page[1]+1;
			$t_page[1] = $t_page[0]+99;
		} //while($t_page[1]<$nb_product_ids){
    }
}