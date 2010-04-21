<?php
class OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    protected function getConfigurableProductParentId()
    {
        if ($this->getItem()->getOptionByCode('cpid')) {
            return $this->getItem()->getOptionByCode('cpid')->getValue();
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
        if (Mage::getStoreConfig('SCP_options/SCP_group/SCP_cart_show_configurable_product_name')
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
        if (Mage::getStoreConfig('SCP_options/SCP_group/SCP_cart_show_custom_options')) {
            $options = parent::getOptionList();
        }

        if (Mage::getStoreConfig('SCP_options/SCP_group/SCP_cart_show_config_product_options')) {
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

    public function getProductThumbnail()
    {
        if (Mage::getStoreConfig('SCP_options/SCP_group/SCP_cart_show_configurable_product_image')
            && $this->getConfigurableProductParentId()) {
           return $this->helper('catalog/image')->init($this->getConfigurableProductParent(), 'thumbnail');
        } else {
            return parent::getProductThumbnail();
            #return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail');
        }
    }
}
