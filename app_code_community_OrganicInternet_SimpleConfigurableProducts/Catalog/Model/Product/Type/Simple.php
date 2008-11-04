<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple
    extends Mage_Catalog_Model_Product_Type_Simple
{
    public function prepareForCart(Varien_Object $buyRequest)
    {
        $product = $this->getProduct();

        //Is info_buyRequest needed for simple products?
        $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }

        // set quantity in cart
        $product->setCartQty($buyRequest->getQty());

        return array($product);
    }
}
?>
