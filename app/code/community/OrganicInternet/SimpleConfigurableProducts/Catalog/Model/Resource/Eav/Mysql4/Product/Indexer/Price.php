<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price
{
    public function getChildIdsByParent($parentId)
    {
        $write = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('pr' => $this->getTable('catalog/product_relation')), array('child_id'))
            ->join(
                array('p' => $this->getTable('catalog/product')),
                'pr.parent_id=p.entity_id',
                array('p.type_id')) #we don't need this
            ->where('pr.parent_id=?', $parentId);
        return $write->fetchPairs($select);
    }

    #This is modified to pull in all sibling associated products' tier prices.
    #Without this, indexed price updates only consider the tier prices of the specific
    #associated product that's being saved, and it's parent
    #This will often result in a non-lowest tier price being displayed, or no tier price being
    #displayed when there is one (on a sibling)
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

        $this->cloneIndexTable(true);
        $this->_prepareWebsiteDateTable();

        $indexer = $this->_getIndexer($data['product_type_id']);
        $processIds = array($productId);
        if ($indexer->getIsComposite()) {
            $this->_copyRelationIndexData($productId);
            $this->_prepareTierPriceIndex($productId);
            $indexer->reindexEntity($productId);
        } else {
            $parentIds = $this->getProductParentsByChild($productId);

            if ($parentIds) {
                #Mage::log("SCP: catalogProductSave: productId is: " . print_r($processIds, true));
                #Mage::log("SCP: catalogProductSave: parentIds are: " . print_r($parentIds, true));
                $processIds = array_merge($processIds, array_keys($parentIds));

                $siblingIds = array();
                foreach (array_keys($parentIds) as $parentId) {
                    $childIds = $this->getChildIdsByParent($parentId);
                    #Mage::log("SCP: catalogProductSave: For parent_id: $productId, adding childIds of: " . print_r($childIds, true));
                    $siblingIds = array_merge($siblingIds, array_keys($childIds));
                }
                #Mage::log("SCP: catalogProductSave: siblingIds are: " . print_r($siblingIds, true));
                if(count($siblingIds)>0) {
                    $processIds = array_merge($processIds, $siblingIds);
                }
                $this->_copyRelationIndexData(array_keys($parentIds), $productId);
                #Mage::log("SCP: catalogProductSave: getting Tier Prices for : " . print_r($processIds, true));
                $this->_prepareTierPriceIndex($processIds);
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
                $indexer->reindexEntity($productId);
            }
        }

        $this->_copyIndexDataToMainTable($processIds);

        return $this;
    }

}
