<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Model_Mappage extends Mage_Core_Model_Abstract
{
	protected $_lines = array();

	const C_NONE = 'none';
	const C_IMAGE_URL = 'image_url';
	const C_PRODUCT_URL = 'product_url';
	const C_CATEGORY = 'category';
	const C_PRODUCT_ID = 'product_id';
	const C_PRODUCT_PRICE = 'current_price';
	const C_PRODUCT_PRICE_BARRE = 'normal_price';
	const C_PRODUCT_PRICE_PROMO = 'promo_price';
	const C_STOCK_QTY = 'stock_qty';
	const C_CURRENCY = 'currency';

	const C_PPAGE = 100;

	protected function _construct()
    {
        $this->_init('comparer/mappage');
    }

	public function addLine($object)
	{
		$this->_lines[] = $object;
	}

	public function getLines()
	{
		return $this->_lines;
	}

	public function canDelete()
	{
		return true;
	}
}