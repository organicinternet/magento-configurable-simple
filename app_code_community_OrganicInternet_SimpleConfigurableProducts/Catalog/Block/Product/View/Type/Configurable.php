<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{

    public function getJsonConfig()
    {
        $childProducts = array();
        $attributes = array();
        $options = array();
        $store = Mage::app()->getStore();
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = $product->getFinalPrice();

            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttribute->getId()])) {
                    $options[$productAttribute->getId()] = array();
                }

                if (!isset($options[$productAttribute->getId()][$attributeValue])) {
                    $options[$productAttribute->getId()][$attributeValue] = array();
                }
                $options[$productAttribute->getId()][$attributeValue][] = $productId;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($this->getProduct()->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
               'id'        => $productAttribute->getId(),
               'code'      => $productAttribute->getAttributeCode(),
               'label'     => $attribute->getLabel(),
               'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }

                    $info['options'][] = array(
                        'id'    => $value['value_index'],
                        'label' => $value['label'],
                        'products'   => isset($options[$attributeId][$value['value_index']]) ? $options[$attributeId][$value['value_index']] : array(),
                    );
                }
            }

            if($this->_validateAttributeInfo($info)) {
               $attributes[$attributeId] = $info;
            }
        }
        /*echo '<pre>';
        print_r($this->_prices);
        echo '</pre>';die();*/
        $config = array(
            'attributes'=> $attributes,
            'template'  => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
//            'prices'    => $this->_prices,
            'basePrice' => $this->_registerJsPrice($this->_convertPrice($this->getProduct()->getFinalPrice())),
            'oldPrice'  => $this->_registerJsPrice($this->_convertPrice($this->getProduct()->getPrice())),
            'productId' => $this->getProduct()->getId(),
            'chooseText'=> Mage::helper('catalog')->__('Choose option...'),
            'childProducts' => $childProducts,
        );
        //Mage::log($config);
        return Zend_Json::encode($config);
    }
}
?>
