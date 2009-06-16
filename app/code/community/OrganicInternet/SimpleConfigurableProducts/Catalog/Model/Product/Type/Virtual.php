<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Virtual
    extends Mage_Catalog_Model_Product_Type_Virtual
{
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }
        return array($product);
    }
}
