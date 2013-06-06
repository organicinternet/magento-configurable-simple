<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Product_Indexer_Price
    extends Mage_Catalog_Model_Resource_Product_Indexer_Price
{
    /**
     * Get an array of child IDs by parent product ID
     * 
     * @param integer $parentId
     * 
     * @return array child_id => child_product type_id
     */
    private function getChildIdsByParent($parentId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('p' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('pr' => $this->getTable('catalog/product_relation')),
                'pr.child_id=p.entity_id',
                array('p.type_id'))
            ->where('pr.parent_id=?', $parentId);
        return $read->fetchPairs($select);
    }

    /**
     * Get product type from product id
     * 
     * catalog_product_entity has the product type.  Not exactly sure why
     * we're using a join here, but it works.
     * 
     * @param integer $id
     * @return string Product type
     */
    private function getProductTypeById($id)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('pr' => $this->getTable('catalog/product_relation')), array('parent_id'))
            ->join(
                array('p' => $this->getTable('catalog/product')),
                'pr.parent_id=p.entity_id',
                array('p.type_id'))
            ->where('pr.parent_id=?', $id);
        $data = $read->fetchRow($select);
        return $data['type_id'];
    }


    /**
     * Modified to pull in all sibling associated products' tier prices and
     * to reindex child tier prices when a parent is saved.
     * 
     * Process product save.
     * Method is responsible for index support
     * when product was saved and changed attribute(s) has an effect on price.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        $productId = $event->getEntityPk();
        $data = $event->getNewData();

        /**
         * Check if price attribute values were updated
         */
        if (!isset($data['reindex_price'])) {
            return $this;
        }
        
        $this->clearTemporaryIndexTable();
        $this->_prepareWebsiteDateTable();

        $indexer = $this->_getIndexer($data['product_type_id']);
        $processIds = array($productId);
        if ($indexer->getIsComposite()) {
            if ($this->getProductTypeById($productId) == 'configurable') {
                $children = $this->getChildIdsByParent($productId);
                $processIds = array_merge($processIds, array_keys($children));
                //Ignore tier and group price data for actual configurable product
                $tierPriceIds = array_keys($children);
            } else {
                $tierPriceIds = $productId;
            }
            $this->_copyRelationIndexData($productId);
            $this->_prepareTierPriceIndex($tierPriceIds);
            $this->_prepareGroupPriceIndex($tierPriceIds);
            $indexer->reindexEntity($productId);
        } else {
            $parentIds = $this->getProductParentsByChild($productId);
            if ($parentIds) {
                $processIds = array_merge($processIds, array_keys($parentIds));
                $siblingIds = array();
                foreach (array_keys($parentIds) as $parentId) {
                    $childIds = $this->getChildIdsByParent($parentId);
                    $siblingIds = array_merge($siblingIds, array_keys($childIds));
                }
                if(count($siblingIds)>0) {
                    $processIds = array_unique(array_merge($processIds, $siblingIds));
                }
                $this->_copyRelationIndexData(array_keys($parentIds), $productId);
                $this->_prepareTierPriceIndex($processIds);
               	$this->_prepareGroupPriceIndex($processIds);
                $indexer->reindexEntity($productId);

                $parentByType = array();
                foreach ($parentIds as $parentId => $parentType) {
                    $parentByType[$parentType][$parentId] = $parentId;
                }

                foreach ($parentByType as $parentType => $entityIds) {
                    $this->_getIndexer($parentType)->reindexEntity($entityIds);
                }
            } else {
                $this->_prepareTierPriceIndex($productId);
                $this->_prepareGroupPriceIndex($productId);
                $indexer->reindexEntity($productId);
            }
        }

        $this->_copyIndexDataToMainTable($processIds);

        return $this;
    }

}
