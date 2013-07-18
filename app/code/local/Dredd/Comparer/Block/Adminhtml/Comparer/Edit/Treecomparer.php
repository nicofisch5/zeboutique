<?php
class Dredd_Comparer_Block_Adminhtml_Comparer_Edit_Treecomparer extends Mage_Core_Block_Template
{

    protected $_withProductCount;
	protected $_limitTo;
	protected $_textLimit;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/category/tree.phtml');
        $this->_withProductCount = true;
		$this->_textLimit = 0;
		$this->_limitTo = 0;
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    protected function _getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    public function getCategoryCollection()
    {
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name')
				->addAttributeToSort('name', 'ASC')
                ->setProductStoreId($this->getRequest()->getParam('store', $this->_getDefaultStoreId()))
                ->setLoadProductCount($this->_withProductCount);

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getStoreSwitcherHtml()
    {
        if (Mage::app()->isSingleStoreMode()) {
            return '';
        }
        return $this->getChildHtml('store_switcher');
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return 1;
    }

    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return 1;
    }

    public function getNodesUrl()
    {
        return $this->getUrl('*/catalog_category/jsonTree');
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/catalog_category/edit', array('_current'=>true, 'id'=>null, 'parent'=>null));
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_category/move', array('store'=>$this->getRequest()->getParam('store')));
    }

    public function getRoot()
    {
        $root = $this->getData('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = 1;
            }

            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->load();
            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != 1) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == 1) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->setData('root', $root);
        }

        return $root;
    }

    public function getTreeArray()
	{
		return $rootArray = $this->_getNodeJson($this->getRoot());
	}

	public function getTreeJson()
    {
        $rootArray = $this->_getNodeJson($this->getRoot());
        $json = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach (Mage::app()->getStores() as $store) {
            	$ids[] = $store->getRootCategoryId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }

    protected function _getNodeJson($node, $level=0)
    {
        
		$item = array();
		
		$item['text'] = $this->buildNodeName($node);
		
        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
		// disabled ou enabled category du noeud 
        // $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
		$item['cls'] = 'folder active-category';
        $item['allowDrop'] = true;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = ($node->getLevel()==1 && $rootForStores) ? false : true;
        if ($node->hasChildren()) {
            $item['children'] = array();
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level+1);
            }
        }
        return $item;
    }
	
	/**
     * Get category name
     *
     * @param Varien_Object $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->htmlEscape($node->getName());
        if ($this->_withProductCount) {
             $result .= ' (' . $node->getProductCount() . ')';
        }
        return $result;
    }
}