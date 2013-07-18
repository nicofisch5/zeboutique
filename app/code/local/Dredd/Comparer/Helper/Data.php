<?php
class Dredd_Comparer_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getFileName($comparer, $toUpperCase = true)
	{
		$filename = '';
		$modelStore = Mage::getModel('core/store')->load($comparer->getStoreId());
		if ($modelStore->getId()) {
			$baseUrl = $modelStore->getBaseUrl();
			
			$modelStoreGroup = Mage::getModel('core/store_group')->load($modelStore->getGroupId());
			
			$name = $modelStoreGroup->getName();
			$code = $modelStore->getCode();
			
			//Langue
			$lg = Mage::getStoreConfig('general/locale/code', $modelStore->getId());
			$t_lg = explode('_', $lg);
			
			$mappage = Mage::getModel('comparer/mappage')->load($comparer->getData('comparer_mappage_id'));
			$filename = $name.'-'.$t_lg[0].'-'.$mappage->getData('comparer_mappage_name'); //StoreNom+Langue+type
		}

		$old = array('è','à','é','î','ù',' ', '|', '#', ')', '(', '}', '}', '+', '*', '/', '$', '~');
		$new = array('e','a','e','i','u','', '', '', '', '', '', '', '', '', '', '', '');
		$name = str_replace($old, $new, $name);
		$filename = $comparer->getName().'-'.$filename;
		$filename = str_replace(' ', '-', $filename);

		$filename = ($toUpperCase) ? strtoupper($filename) : $filename;

		$filename .= '.csv';
		//***** raha hatao texte
		//$filename .= '.txt';
		return $filename;
	}

	public function generateCsv($object)
	{
		$filename = $this->getFileName($object);
		$object->setFilename($filename);

		$mappage = Mage::getModel('comparer/mappage')->load($object->getData('comparer_mappage_id'));
		$separator = $mappage->getComparerMappageSeparator();
		$header = $mappage->getComparerMappageHeader();

		$lines = Mage::getModel('comparer/mappageline')->getCollection()
				->addMappageFilter($mappage->getId())
				->setOrder('sort_order', 'ASC');
		$csv = '';
		if(trim($header)!='' && str_replace(' ','', $header) != '') {
			$csv .= $header."\r\n";
		}
		$i = 0;

		if($separator == '\t') $separator = '	';
		/*
		foreach($lines as $line){
			if($i > 0) $csv .= $separator . utf8_decode($line->getCsv());
			else $csv .= utf8_decode($line->getCsv());
			$i++;
		}
		*/
		foreach($lines as $line){
			if($i > 0) $csv .= $separator . $line->getCsv();
			else $csv .= $line->getCsv();
			$i++;
		}
		file_put_contents(BP . DS . 'comparateur' . DS . $filename, $csv);
	}

	public function formatHTML ($v_html, $length) 
	{
		$html = strip_tags($v_html);
		$html = trim($html);
		
		$html = str_replace(chr(9), " ", $html); 
		$html = str_replace(chr(10), " ", $html);
		$html = str_replace(chr(13), " ", $html);
                $html = str_replace(";", " ", $html);
		
		mb_internal_encoding("UTF-8");
		if (mb_strlen ($html) > $length && $length > 0) {
			$html = mb_substr($html, 0, $length) . " ";
                        $html .= "...";
		}

		return $html;
	}

	public function getFrequencyOptions()
	{
		return array(
			Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY => Mage::helper('sitemap')->__('Daily'),
        	Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY => Mage::helper('sitemap')->__('Weekly'),
        	Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY => Mage::helper('sitemap')->__('Monthly')
		);
	}

	public function getFrequencyHash()
	{
		return array(
			array('label' => Mage::helper('sitemap')->__('Daily'), 'value' => Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY),
			array('label' => Mage::helper('sitemap')->__('Weekly'), 'value' => Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY),
			array('label' => Mage::helper('sitemap')->__('Monthly'), 'value' => Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
		);
	}

        /*
         * Recuperer tous les attributs d'un produit données
         */
         protected function _recupAttrib($attributes,$product){

				$attributByGroup = array();
				$groupName = array();
				$groupIds = array();
				$exclAttr_Arr = array();
				$inclAttr_Arr = array();

			$attributeSet = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
			$attributeSetId = $attributeSet->getAttributeSetId();
			$attributeSetName = $attributeSet->getAttributeSetName();

			$attributeGroup = Mage::getModel('eav/entity_attribute_group')->getResourceCollection()
									->setAttributeSetFilter($attributeSet)
									->load();
			if(isset($attributeGroup)){
				foreach($attributeGroup as $group){
					if(!(in_array($group->getAttributeGroupID(), $groupIds))){
					}
						foreach($attributes as $attribute){

							if(! (($attribute->getAttributeCode()) == ($group->getAttributeGroupID()))){
								//array_push($inclAttr_Arr, $attribute->getAttributeCode());			//empiler dans $exclAtt_Arr les autre attributs ki n'appartienne pas � notre groupe d'attribut
							}
							else{
								//array_push($exclAttr_Arr, $attribute->getAttributeCode());
							}
						}
				}
			}
			return '';
	}



}