<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Adds an additional indexed_price column to as the regular price gets over-ridden elsewhere\
     * 
     * This field seems to only appear when the collection has ->addPriceData();
     * 
     * @see Mage_Catalog_Model_Resource_Product_Collection::_productLimitationJoinPrice()
     */
    protected function _productLimitationPrice($joinLeft = false)
    {
        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index'])) {
            return $this;
        }

        $helper     = Mage::getResourceHelper('core');
        $connection = $this->getConnection();
	$select     = $this->getSelect();
        $joinCond = join(' AND ', array(
            'price_index.entity_id = e.entity_id',
            $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['price_index'])) {
        	$least       = $connection->getLeastSql(array('price_index.min_price', 'price_index.tier_price'));
            $minimalExpr = $connection->getCheckSql('price_index.tier_price IS NOT NULL',
                $least, 'price_index.min_price');
            $indexedExpr = new Zend_Db_Expr('price_index.price');
            $colls = array('indexed_price'=>$indexedExpr,'price', 'tax_class_id', 'final_price', 
            	'minimal_price'=>$minimalExpr , 'min_price', 'max_price', 'tier_price');
            $tableName = array('price_index' => $this->getTable('catalog/product_index_price'));
	    if ($joinLeft) {
                $select->joinLeft($tableName, $joinCond, $colls);
            } else {
                $select->join($tableName, $joinCond, $colls);
            }
            // Set additional field filters
            foreach ($this->_priceDataFieldFilters as $filterData) {
                $select->where(call_user_func_array('sprintf', $filterData));
            }

        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        //Clean duplicated fields
        $helper->prepareColumnsList($select);

        return $this;
    }
}
