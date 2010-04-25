<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product
    extends Mage_Catalog_Model_Product
{
    public function getMaxPossibleFinalPrice()
    {
        if(is_callable(array($this->getPriceModel(), 'getMaxPossibleFinalPrice'))) {
            return $this->getPriceModel()->getMaxPossibleFinalPrice($this);
        } else {
            #return $this->_getData('minimal_price');
            return parent::getMaxPrice();
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
