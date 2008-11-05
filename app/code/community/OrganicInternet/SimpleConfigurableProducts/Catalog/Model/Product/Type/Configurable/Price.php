<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Configurable_Price
    extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    public function getPrice($product)
    {
        return $this->getFinalPrice(1, $product);
    }

    //We don't want to show a separate 'minimal' price for configurable products.
    public function getMinimalPrice($product)
    {
        return $this->getPrice($product);
    }

    //Returns the lowest possible price.
    //Basic 'price' (as entered in admin interface) is not used for configurable
    //products now.
    public function getFinalPrice($qty=null, $product)
    {
        //Take min child final price.
        $childPrices = array();
        foreach($product->getTypeInstance()->getUsedProducts() as $childProduct) {
            $childPrices[] = $childProduct->getFinalPrice();
            //Mage::log("getFinalPrice, examining child: " . $childProduct->getId());
        }
        //It's possible for a configurable product to have no children if, for
        //example, and admin user is in the process of creating it and hasn't
        //yet added any children but has marked it as enabled.  We currently
        //return 0 for the price in this case. This may need reconsidering.
        if (count($childPrices) == 0) {
            $product->setPrice(0);
            return 0;
        }
        $childPrice = min($childPrices);
        $product->setPrice($childPrice);
        return $childPrice;
    }


    //Force tier pricing to be empty for configurable products:
    public function getTierPrice($qty=null, $product)
    {
        return array();
    }

}
?>
