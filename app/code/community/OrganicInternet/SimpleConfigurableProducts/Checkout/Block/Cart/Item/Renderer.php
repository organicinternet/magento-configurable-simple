<?php
class OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    private $_parentProduct = null;

    protected function getConfigurableProductParentId()
    {
        if ($this->getItem()->getOptionByCode('cpid')) {
            return $this->getItem()->getOptionByCode('cpid')->getValue();
        }
        return null;
    }

    protected function getConfigurableProductParent()
    {
        if ($this->_parentProduct) {
            return $this->_parentProduct;
        } else {
            $pid = $this->getConfigurableProductParentId();
            $this->_parentProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($pid);
            return $this->_parentProduct;
        }
    }

    public function getProductUrl()
    {
        if ($this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getProductUrl();
        } else {
            return $this->getProduct()->getProductUrl();
        }
    }

    /*
        This forces the image of the configurable product parent to be used in
        place of the simple product's image, if the simple product has a
        configurable product parent.
    */
    public function getProductThumbnail()
    {
        if ($this->getConfigurableProductParentId()) {
            return $this->helper('catalog/image')->init($this->getConfigurableProductParent(), 'thumbnail');
        } else {
            return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail');
        }
    }
}
