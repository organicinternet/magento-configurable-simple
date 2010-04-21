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
    
    public function isVisibleInSiteVisibility()
    {
        #Force visible any simple products which have a parent conf product.
        #this will only apply to products which have been added to the cart
        if(is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId')) 
           && $this->getTypeInstance()->hasConfigurableProductParentId()) {
           return true;
        } else {
            return parent::isVisibleInSiteVisibility();
        }
    }
}
