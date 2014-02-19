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

require_once 'Amasty/Catcopy/controllers/Adminhtml/IndexController.php';


class Zeboutique_Catcopy_Adminhtml_IndexController extends Amasty_Catcopy_Adminhtml_IndexController
{
    /**
     * Move products
     *
     * @return void
     */
    public function moveAction()
    {
        $this->_initAction();
        $categoryChildrenCount  = array();
        $idRemapping            = array();

        $categoryId = Mage::app()->getRequest()->getParam('id');
        $destCategoryId = Mage::app()->getRequest()->getParam('parent_category_id');

        if (!$destCategoryId) {
            $this->_getSession()->addError(
                Mage::helper('amcatcopy')->__('Destination category not selected')
            );
            $this->_redirect('adminhtml/catalog_category/index');
            return;
        }

        $destCategory =Mage::getModel('catalog/category')->load($destCategoryId);
        $category = Mage::getModel('catalog/category')->load($categoryId);

        if (!$category->getId() || !$destCategory->getId()) {
            $this->_redirect('adminhtml/catalog_category/index');
        }

        $currentProductIds = $destCategory->getProductsPosition();
        $addProductIds = $category->getProductsPosition();

        // Operations on destination category
        $futureProducts = $currentProductIds + $addProductIds;
        $destCategory->setPostedProducts($futureProducts);

        // Operations on source category
        $category->setPostedProducts(array());

        Mage::getModel('core/resource_transaction')
            ->addObject($category)
            ->addObject($destCategory)
            ->save();

        $this->_getSession()->addSuccess(
            Mage::helper('amcatcopy')->__('Products copied from %s to %s', $category->getName(), $destCategory->getName())
        );

        $this->_redirect('adminhtml/catalog_category/index', array('duplicated' => $category->getId()));
    }

}