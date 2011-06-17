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


    public function getProductUrl($useSid = null)
    {
        if(is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId'))
            && $this->getTypeInstance()->hasConfigurableProductParentId()) {

            $confProdId = $this->getTypeInstance()->getConfigurableProductParentId();
            return Mage::getModel('catalog/product')->load($confProdId)->getProductUrl();

        } else {
            return parent::getProductUrl($useSid);
        }
    }
}
