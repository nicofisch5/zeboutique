<?php
class Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Pcontainer extends Mage_Core_Block_Template
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('comparer/pcontainer.phtml');
	}

	public function _beforeToHtml()
    {        
        $this->setChild('product', $this->getLayout()->createBlock('comparer/adminhtml_comparer_edit_product', 'products.grid'));     
        return parent::_beforeToHtml();
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

	public function getListUrl()
	{
		return $this->getUrl('*/*/', array('_current'=>false,'id'=>null, 'type'=>null, 'store'=>null, 'key'=>Mage::getSingleton('adminhtml/url')->getSecretKey()));
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