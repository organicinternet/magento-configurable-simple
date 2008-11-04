<?php

class OrganicInternet_SimpleConfigurableProducts_Block_ProductJsExtension
    extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        //Not the ideal way to add a js file, but it's a safe way to load this js after varien/product.js
        return '<script type="text/javascript" src="' . $this->getSkinUrl('js/product_extension.js') . '" language="javascript"></script>';
    }
}
