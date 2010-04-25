<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    #Adds an additional price 'indexed_price' to stop the indexed_price being 
    #overidden elsewhere by the normal product price.
    protected function _productLimitationJoinPrice()
    {
        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index'])) {
            return $this;
        }

        $connection = $this->getConnection();

        $joinCond = $joinCond = join(' AND ', array(
            'price_index.entity_id = e.entity_id',
            $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['price_index'])) {
            $minimalExpr = new Zend_Db_Expr(
                'IF(`price_index`.`tier_price`, LEAST(`price_index`.`min_price`, `price_index`.`tier_price`), `price_index`.`min_price`)'
            );
            $mdExpr = new Zend_Db_Expr('price_index.price');
            $this->getSelect()->join(
                array('price_index' => $this->getTable('catalog/product_index_price')),
                $joinCond,
                array('indexed_price'=>$mdExpr,'price', 'final_price', 'minimal_price'=>$minimalExpr , 'min_price', 'max_price', 'tier_price'));

        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }

        return $this;
    }
}
