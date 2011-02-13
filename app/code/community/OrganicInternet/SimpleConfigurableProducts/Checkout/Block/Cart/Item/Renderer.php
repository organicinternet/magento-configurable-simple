<?php
class OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    protected function getConfigurableProductParentId()
    {
        if ($this->getItem()->getOptionByCode('cpid')) {
            return $this->getItem()->getOptionByCode('cpid')->getValue();
        }
        #No idea why in 1.5 the stuff in buyRequest isn't auto-decoded from info_buyRequest
        #but then it's Magento we're talking about, so I've not a clue what's *meant* to happen.
        try {
            $buyRequest = unserialize($this->getItem()->getOptionByCode('info_buyRequest')->getValue());
            if(!empty($buyRequest['cpid'])) {
                return $buyRequest['cpid'];
            }
        } catch (Exception $e) {
        }
        return null;
    }

    protected function getConfigurableProductParent()
    {
        return Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($this->getConfigurableProductParentId());
    }

    public function getProduct()
    {
        return Mage::getModel('catalog/product')
           ->setStoreId(Mage::app()->getStore()->getId())
                ->load($this->getItem()->getProductId());
    }

    public function getProductName()
    {
        if (Mage::getStoreConfig('SCP_options/cart/show_configurable_product_name')
            && $this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getName();
        } else {
            return parent::getProductName();
        }
    }


    /* Bit of a hack this - assumes configurable parent is always linkable */
    public function hasProductUrl()
    {
        if ($this->getConfigurableProductParentId()) {
            return true;
        } else {
            return parent::hasProductUrl();
        }
    }

    public function getProductUrl()
    {
        if ($this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getProductUrl();
        } else {
            return parent::getProductUrl();
            #return $this->getProduct()->getProductUrl();
        }
    }

    public function getOptionList()
    {
        $options = false;
        if (Mage::getStoreConfig('SCP_options/cart/show_custom_options')) {
            $options = parent::getOptionList();
        }

        if (Mage::getStoreConfig('SCP_options/cart/show_config_product_options')) {
            if ($this->getConfigurableProductParentId()) {
                $attributes = $this->getConfigurableProductParent()
                    ->getTypeInstance()
                    ->getUsedProductAttributes();
                foreach($attributes as $attribute) {
                    $options[] = array(
                        'label' => $attribute->getFrontendLabel(),
                        'value' => $this->getProduct()->getAttributeText($attribute->getAttributeCode()),
                        'option_id' => $attribute->getId(),
                    );
                }
            }
        }
        return $options;
    }

    /*
    Logic is:
    If not SCP product, use normal thumbnail behaviour
    If is SCP product, and admin value is set to use configurable image, do so
    If is SCP product, and admin value is set to use simple image, do so,
      but 'fail back' to configurable image if simple image is placeholder
    If logic says to use it, but configurable product image is placeholder, then
      just display placeholder

    */
    public function getProductThumbnail()
    {
        #If product not added via SCP, use default behaviour
        if (!$this->getConfigurableProductParentId()) {
           return parent::getProductThumbnail();
        }


        #If showing simple product image
        if (!Mage::getStoreConfig('SCP_options/cart/show_configurable_product_image')) {
            $product = $this->getProduct();
            #if product image is not a thumbnail
            if($product->getData('thumbnail') && ($product->getData('thumbnail') != 'no_selection')) {
                return $this->helper('catalog/image')->init($product, 'thumbnail');
            }
        }

        #If simple prod thumbnail image is placeholder, or we're not using simple product image
        #show configurable product image
        $product = $this->getConfigurableProductParent();
        return $this->helper('catalog/image')->init($product, 'thumbnail');
    }
}
