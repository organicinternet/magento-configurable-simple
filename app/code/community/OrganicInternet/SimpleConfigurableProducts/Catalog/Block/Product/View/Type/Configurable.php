<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    public function getJsonConfig()
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
            );

            if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
                $childProducts[$productId]["productName"] = $product->getName();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
                $childProducts[$productId]["description"] = $product->getDescription();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
                $childProducts[$productId]["shortDescription"] = $product->getShortDescription();
            }

            if (Mage::getStoreConfig('SCP_options/product_page/change_attributes')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                    ->setProduct($product)
                    ->toHtml();
            }

            #if image changing is enabled..
            if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
                #but dont bother if fancy image changing is enabled
                if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                    #If image is not placeholder...
                    if(strpos((string)Mage::helper('catalog/image')->init($product, 'image'), (string)Mage::helper('catalog/image')->getPlaceHolder($product)) === FALSE) {
                        #add child product image url to json spConfig var
                        $childProducts[$productId]["imageUrl"] = (string)Mage::helper('catalog/image')->init($product, 'image');
                    }
                }
            }
        }

        //Remove any existing option prices.
        //Removing holes out of existing arrays is not nice,
        //but it keeps the extension's code separate so if Varien's getJsonConfig
        //is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }

        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $this->getProduct()->getName();
        $config['description'] = $this->getProduct()->getDescription();
        $config['shortDescription'] = $this->getProduct()->getShortDescription();
        $config["imageUrl"] = (string)Mage::helper('catalog/image')->init($this->getProduct(), 'image');

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($this->getProduct())
            ->toHtml();

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
                $config["imageZoomer"] = $childBlock->setTemplate('catalog/product/view/media.phtml')
                    ->setProduct($this->getProduct())
                    ->toHtml();
            }
        }

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');

        }
        //Mage::log($config);
        return Zend_Json::encode($config);
    }
}
