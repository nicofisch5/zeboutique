<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
* @licence Commercial
*/
class Dredd_Comparer_Block_Adminhtml_Comparer_Express extends Mage_Core_Block_Template
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('comparer/express.phtml');
	}
	public function getInStock()
	{
		if( Mage::registry('comparer')->getId() ) return Mage::registry('comparer')->getInStock();
		return 0;
	}

	public function getAjaxUrl()
	{
		return $this->getUrl('*/*/product', array('_current'=>true, '_secure'=>true));
	}

	public function getPostUrl()
	{
		return $this->getUrl('*/*/request', array('_current'=>true, '_secure'=>true));
	}

	public function getComparer()
	{
		return Mage::registry('comparer');
	}

	public function getMappageId()
	{
		return $this->getComparer()->getComparerMappageId();
	}

	public function getStoreId()
	{
		return $this->getComparer()->getStoreId();
	}
}