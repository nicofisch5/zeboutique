<?php

/**
 * 1997-2014 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <modules@quadra-informatique.fr>
 * @copyright 1997-2014 Quadra Informatique
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Quadra_Be2bill_Block_Checkout_Success_Crosssell extends Mage_Catalog_Block_Product_Abstract
{

    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;

    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();
            $ninProductIds = $this->_getOrderProductIds();
            if ($ninProductIds) {
                $lastAdded = (int) $this->_getLastAddedProductId();
                if ($lastAdded) {
                    $collection = $this->_getCollection()
                            ->addProductFilter($lastAdded);
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();

                    foreach ($collection as $item) {
                        $ninProductIds[] = $item->getId();
                        $items[] = $item;
                    }
                }

                if (count($items) < $this->_maxItemCount) {
                    $filterProductIds = array_merge($this->_getOrderProductIds(), $this->_getOrderProductIdsRel());
                    $collection = $this->_getCollection()
                            ->addProductFilter($filterProductIds)
                            ->addExcludeProductFilter($ninProductIds)
                            ->setPageSize($this->_maxItemCount - count($items))
                            ->setGroupBy()
                            ->setPositionOrder()
                            ->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }

            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Count items
     *
     * @return int
     */
    public function getItemCount()
    {
        return count($this->getItems());
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        $params['product'] = $product->getId();
        return $this->getUrl('be2bill/checkout_oneclick/orderProduct', array_merge($params, $additional));
    }

    /**
     * Get ids of products that are in order
     *
     * @return array
     */
    protected function _getOrderProductIds()
    {
        $ids = $this->getData('_order_product_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach ($this->getOrder()->getAllItems() as $item) {
                if ($product = $item->getProduct()) {
                    $ids[] = $product->getId();
                }
            }
            $this->setData('_order_product_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Cart
     * For example simple product as part of Grouped product
     *
     * @return array
     */
    protected function _getOrderProductIdsRel()
    {
        $productIds = array();
        foreach ($this->getOrder()->getAllItems() as $orderItem) {
            $productTypeOpt = $orderItem->getOptionByCode('product_type');
            if ($productTypeOpt instanceof Mage_Sales_Model_Order_Item_Option && $productTypeOpt->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE && $productTypeOpt->getProductId()
            ) {
                $productIds[] = $productTypeOpt->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * Get last product ID that was added to order and remove this information from session
     *
     * @return int
     */
    protected function _getLastAddedProductId()
    {
        return Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
    }

    /**
     * Get order instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        return Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * Get crosssell products collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    protected function _getCollection()
    {
        $collection = Mage::getModel('catalog/product_link')->useCrossSellLinks()
                ->getProductCollection()
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addStoreFilter()
                ->setPageSize($this->_maxItemCount);
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }

    public function isOrderPaidWithBe2bill()
    {
        $paymentMethod = $this->getOrder()->getPayment()->getMethod();
        return preg_match("/be2bill/i", $paymentMethod);
    }

    public function displayPostPayment()
    {
        return ($this->getItemCount() && ($this->getOrder()->getPayment()->getAdditionalInformation('create_oneclick') == 'yes' || $this->getOrder()->getPayment()->getAdditionalInformation('use_oneclick') == 'yes') && $this->isOrderPaidWithBe2bill() && Mage::getStoreConfig('be2bill/be2bill_oneclick_config/active_postpayment')) ? true : false;
    }

    public function canSeveralOneclick($product)
    {
        if (!$product->getBe2billEnableOcSevPayment())
            return false;

        return true;
    }

}
