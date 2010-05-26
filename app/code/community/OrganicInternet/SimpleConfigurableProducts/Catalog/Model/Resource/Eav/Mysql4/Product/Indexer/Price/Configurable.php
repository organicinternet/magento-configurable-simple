<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable
{
    protected function _isManageStock()
    {
        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    }

    #Don't pay any attention to cost of specific conf product options, as SCP doesn't use them
    protected function _applyConfigurableOption()
    {
        return $this;
    }

    #This calculates final price using SCP logic: minimal child product finalprice
    #instead of the just the entered configurable price
    #It uses a subquery/group-by hack to ensure that the various column values are all from the row with the lowest final price.
    #See Kasey Speakman comment here: http://dev.mysql.com/doc/refman/5.1/en/example-maximum-column-group-row.html
    #It's all quite complicated. :/

    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id'))
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_super_link')),
                'l.parent_id = e.entity_id',
                array())
             ->join(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.product_id',
                array())
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '',
                array())
            ->joinLeft(
                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = cis.stock_id AND cisi.product_id = le.entity_id',
                array())
            ->join(
                array('cw' => $this->getTable('core/website')),
                '',
                array('website_id'))
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id',
                array())
            ->join(
                array('csg' => $this->getTable('core/store_group')),
                'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
                array())
            ->join(
                array('cs' => $this->getTable('core/store')),
                'csg.default_store_id = cs.store_id AND cs.store_id != 0',
                array())
            ->join(
                array('pw' => $this->getTable('catalog/product_website')),
                'pw.product_id = le.entity_id AND pw.website_id = cw.website_id',
                array())
            ->joinLeft(
                array('tp' => $this->_getTierPriceIndexTable()),
                'tp.entity_id = le.entity_id AND tp.website_id = cw.website_id'
                    . ' AND tp.customer_group_id = cg.customer_group_id',
                array())
            ->where('e.type_id=?', $this->getTypeId());


        $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'le.entity_id', 'cs.store_id');
        $select->columns(array('tax_class_id' => $taxClassId));

        $price              = $this->_addAttributeToSelect($select, 'price', 'le.entity_id', 'cs.store_id');
        $productStatusExpr  = $this->_addAttributeToSelect($select, 'status', 'le.entity_id', 'cs.store_id');
        $specialPrice       = $this->_addAttributeToSelect($select, 'special_price', 'le.entity_id', 'cs.store_id');
        $specialFrom        = $this->_addAttributeToSelect($select, 'special_from_date', 'le.entity_id', 'cs.store_id');
        $specialTo          = $this->_addAttributeToSelect($select, 'special_to_date', 'le.entity_id', 'cs.store_id');
        $curentDate         = new Zend_Db_Expr('cwd.date');


        if ($this->_isManageStock()) {
            $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,' . ' 1, cisi.is_in_stock)');
        } else {
            $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,' . 'cisi.is_in_stock, 1)');
        }
        $isInStockExpr = new Zend_Db_Expr("IF({$stockStatusExpr}, 1, 0)");

        $isValidChildProductExpr = new Zend_Db_Expr("LEAST({$isInStockExpr}, {$productStatusExpr})");

        $finalPriceExpr = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
            . "IF(DATE({$specialFrom}) <= {$curentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
            . "IF(DATE({$specialTo}) >= {$curentDate}, 1, 0)) > 0 AND {$specialPrice} < {$price}, "
            . "{$specialPrice}, {$price})");

        $finalPrice = new Zend_Db_Expr("IF({$isValidChildProductExpr}=1, ${finalPriceExpr}, null)");

        $select->columns(array(
            'orig_price'    => $price,
            'price'         => $finalPrice,
            'min_price'     => $finalPrice,
            'max_price'     => $finalPrice,
            'tier_price'    => new Zend_Db_Expr('tp.min_price'),
            'base_tier'     => new Zend_Db_Expr('tp.min_price'),
        ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        #Inner select order needs to be:
        #1st) $finalPrice but force NULLs to come last. (if there is no final price it's not the lowest)
        #2nd) $price, in case all finalPrices are NULL.
        $sortExpr = new Zend_Db_Expr("${finalPrice} IS NULL ASC, ${finalPrice} ASC, ${price} ASC");
        #$select->order($finalPrice);
        $select->order($sortExpr);

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));


        #This uses the fact that mysql's 'group by' picks the first row, and the subselect is ordered as we want it
        #Bit hacky, but lots of people do it :)
        $outerSelect = $write->select()
            ->from(array("inner" => $select), 'entity_id')
            ->group(array('inner.entity_id', 'inner.customer_group_id', 'inner.website_id'));

        $outerSelect->columns(array(
            'customer_group_id',
            'website_id',
            'tax_class_id',
            'orig_price',
            'price',
            'min_price',
            'max_price'     => new Zend_Db_Expr('MAX(inner.max_price)'),
            'tier_price',
            'base_tier'));

        $query = $outerSelect->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);

        #Mage::log("SCP Price inner query: " . $select->__toString());
        #Mage::log("SCP Price outer query: " . $outerSelect->__toString());

        /**
         * Add possibility modify prices from external events
         */
        $select = $write->select()
            ->join(array('wd' => $this->_getWebsiteDateTable()),
                'i.website_id = wd.website_id',
                array());
        Mage::dispatchEvent('prepare_catalog_product_price_index_table', array(
            'index_table'       => array('i' => $this->_getDefaultFinalPriceTable()),
            'select'            => $select,
            'entity_id'         => 'i.entity_id',
            'customer_group_id' => 'i.customer_group_id',
            'website_id'        => 'i.website_id',
            'website_date'      => 'wd.date',
            'update_fields'     => array('price', 'min_price', 'max_price')
        ));

        return $this;
    }
}
