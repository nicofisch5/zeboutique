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
 * @package     Zeboutique_Catcopy
 * @copyright   Copyright (c) 2014 Zeboutique
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Zeboutique
 */

/**
 * Zeboutique Catcopy
 *
 * @category    Zeboutique
 * @package     Zeboutique_Catcopy
 * @author      Zeboutique
 */ 
class Zeboutique_Catcopy_Block_Adminhtml_Catalog_Category_Duplicate extends Amasty_Catcopy_Block_Adminhtml_Catalog_Category_Duplicate
{
    /**
     * Preparing block layout
     *
     * @return Amasty_Catcopy_Block_Adminhtml_Catalog_Category_Duplicate
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('move_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('amcatcopy')->__('Move Products'),
                    'onclick'   => "$('amcatcopy_category_duplicate_form').action = '".$this->getMoveUrl()."';processCategoryDuplicate();",
                    'class'     => 'save'
                ))
        );

        return $this;
    }

    /**
     * Retrieve Move Button HTML
     *
     * @return string
     */
    public function getMoveButtonHtml()
    {
        return $this->getChildHtml('move_button');
    }

    /**
     * Return save duplicated category url for form submit
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('*/*/move', array('id' => $this->getCategoryId()));
    }
}