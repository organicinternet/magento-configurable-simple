<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product
    extends Mage_Catalog_Model_Product
{
    public function getMinimalPrice()
    {
        if(is_callable(array($this->getPriceModel(), 'getMinimalPrice'))) {
            return $this->getPriceModel()->getMinimalPrice($this);
        } else {
            return $this->_getData('minimal_price');
        }
    }
}
