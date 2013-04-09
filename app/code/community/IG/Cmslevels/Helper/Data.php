<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category   IG
 * @package    IG_Cmslevels
 * @copyright  Copyright (c) 2011-2012 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://www.idealiagroup.com/magento-ext-license.html
 */
 
class IG_Cmslevels_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_GENERAL_ENABLED = 'ig_cmslevels/general/enabled';
	const XML_PATH_GENERAL_LEVEL_SEPARATOR = 'ig_cmslevels/general/level_separator';

	/**
	 * Check if component is enabled or not
	 *
	 * @return bool
	 */
	public function getIsEnabled()
	{
		return Mage::getStoreConfig(self::XML_PATH_GENERAL_ENABLED) ? true : false;
	}
	
	/**
	 * Get CMS level separator
	 *
	 * @return string
	 */
	public function getLevelSeparator()
	{
		return trim(Mage::getStoreConfig(self::XML_PATH_GENERAL_LEVEL_SEPARATOR));
	}
}
