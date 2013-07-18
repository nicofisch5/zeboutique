<?php
/**
* @package Dredd_Comparer
* @author Rija
* @email rija@madadev.fr
*/
class Dredd_Comparer_Model_Croning extends Mage_Core_Model_Abstract
{
	protected function _construct()
    {
        $this->_init('comparer/croning', true);
    }

	public function canDelete()
	{
		return true;
	}
}