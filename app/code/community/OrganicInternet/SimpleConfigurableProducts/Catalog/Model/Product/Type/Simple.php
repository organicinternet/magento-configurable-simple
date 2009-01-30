<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple
    extends Mage_Catalog_Model_Product_Type_Simple
{
    public function prepareForCart(Varien_Object $buyRequest)
    {
        parent::prepareForCart($buyRequest);
        $product = $this->getProduct();
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }
        return array($product);
    }
}
?>
