<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Virtual
    extends Mage_Catalog_Model_Product_Type_Virtual
{
    #Later this should be refactored to live elsewhere probably,
    #but it's ok here for the time being
    private function getCpid()
    {
        $cpid = $this->getProduct()->getCustomOption('cpid');
        if ($cpid) {
            return $cpid;
        }

        $br = $this->getProduct()->getCustomOption('info_buyRequest');
        if ($br) {
            $brData = unserialize($br->getValue());
            if(!empty($brData['cpid'])) {
                return $brData['cpid'];
            }
        }

        return false;
    }


    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }
        return array($product);
    }

    public function hasConfigurableProductParentId()
    {
        $cpid = $this->getCpid();
        Mage::log("cpid: ". $cpid);
        return !empty($cpid);
    }

    public function getConfigurableProductParentId()
    {
        return $this->getCpid();
    }
}
