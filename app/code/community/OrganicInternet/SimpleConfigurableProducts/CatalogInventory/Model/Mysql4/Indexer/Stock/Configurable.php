<?php
class OrganicInternet_SimpleConfigurableProducts_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
    extends Mage_CatalogInventory_Model_Mysql4_Indexer_Stock_Configurable
{    
    #Changes stock status indexing such that:
    #Configurable product's status is ignored
    #If any child product is enabled+in_stock then conf product is in stock
    #Doesn't filter based on children having required custom attributes, as they're fine with SCP
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $adapter  = $this->_getWriteAdapter();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $select  = $adapter->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $select->columns('cw.website_id')
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '',
                array('stock_id'))
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_super_link')),
                'l.parent_id = e.entity_id',
                array())
            ->join(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.product_id',
                array())
            ->joinLeft(
                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = cis.stock_id AND cisi.product_id = le.entity_id',
                array())
            ->joinLeft(
                array('i' => $idxTable),
                'i.product_id = le.entity_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
                array())
            ->columns(array('qty' => new Zend_Db_Expr('0')))
            ->where('cw.website_id != 0')
            ->where('e.type_id = ?', $this->getTypeId())
            ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));

        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'le.entity_id'); #may need to use le.entity_id?

        $psExpr = $this->_addAttributeToSelect($select, 'status', 'le.entity_id', 'cs.store_id');
        $psCond = $adapter->quoteInto($psExpr . '=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        if ($this->_isManageStock()) {
            $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,'
                . ' 1, cisi.is_in_stock)');
        } else {
            $statusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,'
                . 'cisi.is_in_stock, 1)');
        }

        $stockStatusExpr = new Zend_Db_Expr("MAX(LEAST(IF({$psCond}, 1, 0), {$statusExpr}))");

        $select->columns(array(
            'status' => $stockStatusExpr
        ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }
}
