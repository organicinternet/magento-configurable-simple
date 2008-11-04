<?php

class OrganicInternet_SimpleConfigurableProducts_CatalogIndex_Model_Mysql4_Indexer
    extends Mage_CatalogIndex_Model_Mysql4_Indexer
{
    public function reindexFinalPrices($products, $store, $forcedId = null)
    {
        $priceAttribute = Mage::getSingleton('eav/entity_attribute')->getIdByCode('catalog_product', 'price');
        $this->_beginInsert('catalogindex/price', array('entity_id', 'store_id', 'customer_group_id', 'value', 'attribute_id', 'tax_class_id'));

        $productTypes = Mage::getSingleton('catalogindex/retreiver')->assignProductTypes($products);
        foreach ($productTypes as $type=>$products) {
            $retreiver = Mage::getSingleton('catalogindex/retreiver')->getRetreiver($type);
            foreach ($products as $product) {
                if (is_null($forcedId)) {
                    if ($retreiver->areChildrenIndexable(Mage_CatalogIndex_Model_Retreiver::CHILDREN_FOR_PRICES)) {
                        $children = $retreiver->getChildProductIds($store, $product);
                        if ($children) {
                            $this->reindexFinalPrices($children, $store, $product);
                        }
                    }
                }
                //If we're using child prices and we're looking at a child
                //Or we're not using child prices
                if ( ($retreiver->areChildrenIndexable(Mage_CatalogIndex_Model_Retreiver::CHILDREN_FOR_PRICES) && !is_null($forcedId))
                   || !$retreiver->areChildrenIndexable(Mage_CatalogIndex_Model_Retreiver::CHILDREN_FOR_PRICES))
                {
                    foreach (Mage::getModel('catalogindex/retreiver')->getCustomerGroups() as $group) {
                        $finalPrice = $retreiver->getFinalPrice($product, $store, $group);
                        $taxClassId = $retreiver->getTaxClassId($product, $store);
                        $id = $product;
                        if (!is_null($forcedId))
                            $id = $forcedId;

                        if (false !== $finalPrice && false !== $id && false !== $store->getId() && false !== $group->getId() && false !== $priceAttribute) {
                            $this->_insert('catalogindex/price', array($id, $store->getId(), $group->getId(), $finalPrice, $priceAttribute, $taxClassId));
                        }
                    }
                }
            }
        }
        $this->_commitInsert('catalogindex/price');
    }
}
