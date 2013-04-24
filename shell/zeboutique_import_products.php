<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Zeboutique
 * @package     Zeboutique_Shell
 * @copyright   Copyright (c) 2013 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Shell
 *
 * @category    Zeboutique
 * @package     Zeboutique_Shell
 * @author      Zeboutique
 */

require_once 'abstract.php';

abstract class Zeboutique_Shell_ImportProducts extends Mage_Shell_Abstract
{

    protected $_nbSimpleCreated = 0;
    protected $_nbConfigurableCreated = 0;

    /**
     * Construct
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $this->_dirMediaImport = Mage::getBaseDir('media').DS.'import'.DS;
    }

    /**
     * Get CSV stream
     *
     * @return Varien_Io_File
     */
    protected function _getCsvStream()
    {
        $io = new Varien_Io_File();
        $info = pathinfo($this->_filename);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // Skip headers
        $io->streamReadCsv();

        return $io;
    }

    /**
     * Get Connection object
     *
     * @return Object
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('catalog_read');
    }

    /**
     * Get Select object
     *
     * @return Object
     */
    protected function _getSelect()
    {
        return $this->_getConnection()->select();
    }

    /**
     * Get Table
     *
     * @param string $table
     * @return string
     */
    protected function _getTable($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    /**
     * Get Attribute Options
     *
     * @param string $attCode
     * @return array
     */
    protected function _getAttributeOptions($attCode)
    {
        if (!array_key_exists($attCode, $this->_attributeOptions)) {
            $select = $this->_getSelect()
                ->from(array('a' => $this->_getTable('eav/attribute')))
                ->join(array('ao' => $this->_getTable('eav/attribute_option')), 'ao.attribute_id=a.attribute_id', array('ao.option_id'))
                ->where('a.attribute_code = (?)', $attCode)
                ->joinLeft(
                    array('store' => $this->_getTable('eav/attribute_option_value')),
                    "store.option_id=ao.option_id and store.store_id=0",
                    array('value' => "store.value")
                );

            $rawOptions = $this->_getConnection()->fetchAll($select);
            $options = array();
            foreach ($rawOptions as $rawOption) {
                $options[$rawOption['value']] = $rawOption['option_id'];
            }
            $this->_attributeOptions[$attCode] = $options;
        }

        return $this->_attributeOptions[$attCode];
    }

    /**
     * Get Attribute Option Id By Value
     *
     * @param string $attCode
     * @param string $value
     * @return int
     */
    protected function _getAttributeOptionIdByValue($attCode, $value)
    {
        $options = $this->_getAttributeOptions($attCode);
        return $options[$value];
    }

    /**
     * @param string $url
     * @param string $imageName
     * @return Zeboutique_Shell_ImportProducts_Chantefeuille
     */
    protected function _getImageByUrl($url, $imageName)
    {
        copy($url, $this->_dirMediaImport . $imageName);
    }
}