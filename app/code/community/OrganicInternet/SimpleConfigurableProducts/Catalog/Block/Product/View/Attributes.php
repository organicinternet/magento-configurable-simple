<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Attributes extends
    Mage_Catalog_Block_Product_View_Attributes
{
    #Not sure why mage product_view_attributes block extends Mage_Core_Block_Template instead of say
    #Mage_Catalog_Block_Product_View_Abstract, but it means that setProduct($product) won't work, so
    #I've had to add it here.
    public function setProduct($product) {
        $this->_product = $product;
        return $this;
    }
}
