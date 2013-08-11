<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() 
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('amcatcopy')->__('Duplicate Category'), Mage::helper('amcatcopy')->__('Duplicate Category'));
        return $this;
    }
    
    public function duplicateAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('amcatcopy/adminhtml_catalog_category_duplicate', 'template'));
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        $this->_initAction();
        $categoryChildrenCount  = array();
        $idRemapping            = array();
        
        $categoryId             = Mage::app()->getRequest()->getParam('id');
        //echo $categoryId; exit;
        $newParentId            = Mage::app()->getRequest()->getParam('parent_category_id');
        if (!$newParentId)
        {
            // if no parent specify, will use default root
            $newParentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        }
        
        $category = Mage::getModel('catalog/category')->load($categoryId);
               
        //$categoryChildrenCount[$categoryId] = $category->getData('children_count');
        $categoryChildrenCount[] = $categoryId; //$category->getData('children_count');
        
        // saving id for all nodes in branch for copy
        $currentParents = $category->getParentIds();
        if(!empty($currentParents))
        {
            foreach ($currentParents as $parentId)
            {
                $parentModel = Mage::getModel('catalog/category')->load($parentId);
                $categoryChildrenCount[] = $parentId; //$parentModel->getData('children_count');
            }
        }
        
        $oldParentId = $category->getParentId();
        if (!$newParentId || !$categoryId || !$category)
        {
            $this->_redirect('adminhtml/catalog_category/index');
        }

        if (Mage::app()->getRequest()->getParam('copy_products'))
        {
            // copying product relations
            $productIds = $category->getProductsPosition();
            $category->setPostedProducts($productIds);
        }
        
        $category->setData('products_position', array());
        $category->setId(null);
        $category->setUrlKey(Mage::helper('amcatcopy')->suggestUrlKey($category->getUrlKey()));
        $category->save();
        
        Mage::helper('amcatcopy')->copyStoreData($categoryId, $category->getId());
        $idRemapping[$categoryId] = $category->getId();
        
        // moving duplicated category to specified parent
        $tree = Mage::getResourceModel('catalog/category_tree')->load();
        $node = $tree->getNodeById($category->getId());

        $newParentNode  = $tree->getNodeById($newParentId);
        $newParentRootForUpdate = $newParentId;
        //$oldParentRootForUpdate = $categoryId; //$oldParentId;
        $prevNode = $tree->getNodeById($oldParentId);
        $tree->move($node, $newParentNode, $prevNode);

        // handling URL rewrites
        Mage::helper('amcatcopy')->handleUrlRewrites($category);
        
        /**
        * Including subcategories if specified
        */
        
        if (Mage::app()->getRequest()->getParam('include_subcats'))
        {
            $node = $tree->getNodeById($categoryId);
            if ($node->getAllChildNodes())
            {
                foreach ($node->getAllChildNodes() as $subcategory)
                {
                    // preventing duplicate of node category
                    if ($subcategory->getId() != $category->getId())
                    {
                        $subcategory = Mage::getModel('catalog/category')->load($subcategory->getId());
                        $categoryChildrenCount[] = $subcategory->getId(); //$subcategory->getData('children_count');
                        
                        if (Mage::app()->getRequest()->getParam('copy_products'))
                        {
                            // copying product relations
                            $productIds = $subcategory->getProductsPosition();
                            $subcategory->setPostedProducts($productIds);
                        }
                        $subcategoryOldId = $subcategory->getId();
                        //echo $subcategoryOldId;exit;
                        $subcategoryOldParentId = $subcategory->getParentId();
                        $subcategory->setData('products_position', array());
                        $subcategory->setId(null);
                        $subcategory->setUrlKey(Mage::helper('amcatcopy')->suggestUrlKey($subcategory->getUrlKey()));
                        $subcategory->save();
                        Mage::helper('amcatcopy')->copyStoreData($subcategoryOldId, $subcategory->getId());
                        $idRemapping[$subcategoryOldId] = $subcategory->getId();
                        // reloading tree after saving new category
                        $tree = Mage::getResourceModel('catalog/category_tree')->load();
                        
                        // moving new subcategory to newly created parent category
                        if (isset($idRemapping[$subcategoryOldParentId]))
                        {
                            $node = $tree->getNodeById($subcategory->getId());
                            $newParentNode  = $tree->getNodeById($idRemapping[$subcategoryOldParentId]);
                            $prevNode = $tree->getNodeById($subcategory->getParentId());
                            $tree->move($node, $newParentNode, $prevNode);
                        }
                        
                        // handling URL rewrites
                        Mage::helper('amcatcopy')->handleUrlRewrites($subcategory);
                    }
                }
            }
        }

        if (!empty($categoryChildrenCount))
        { 
            foreach ($categoryChildrenCount as $categoryId)
            {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $subCategories = $category->getResource()->getChildren($category, true);
                $childrenCount = count($subCategories);
                $category->setData('children_count', $childrenCount);
                $category->save();
            }
        }

        //update children count of new parent
        $category = Mage::getModel('catalog/category')->load($newParentRootForUpdate);
        $subCategories = $category->getResource()->getChildren($category, true);
        $childrenCount = count($subCategories);
        $category->setData('children_count', $childrenCount);
        $category->save();

        $this->_getSession()->addSuccess(Mage::helper('amcatcopy')->__('Category duplicated'));
        // passing "duplicated" param to focus newly created category
        $this->_redirect('adminhtml/catalog_category/index', array('duplicated' => $category->getId()));
    }
    
    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('amcatcopy/adminhtml_catalog_category_duplicate_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
}
