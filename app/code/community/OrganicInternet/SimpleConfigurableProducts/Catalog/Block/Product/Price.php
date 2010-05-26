<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_Price
    extends Mage_Catalog_Block_Product_Price
{
    #This is overridden as an admittedly nasty hack to not have to change the contents of catalog/product/price.phtml
    #This is because there's no nice way to keep price.phtml in sync between this extension and the magento core version
    #Yes, it's dependent on the value of $htmlToInsertAfter; I'm not aware of a better alternative.
    public function _toHtml() {
        $htmlToInsertAfter = '<div class="price-box">';
        if ($this->getTemplate() == 'catalog/product/price.phtml') {
            $product = $this->getProduct();
            if (is_object($product) && $product->isConfigurable()) {
                $extraHtml = '<span class="label" id="configurable-price-from-'
                . $product->getId()
                . $this->getIdSuffix()
                . '"><span class="configurable-price-from-label">';

                if ($product->getMaxPossibleFinalPrice() != $product->getFinalPrice()) {
                    $extraHtml .= $this->__('Price From:');
                }
                $extraHtml .= '</span></span>';
                $priceHtml = parent::_toHtml();
                #manually insert extra html needed by the extension into the normal price html
                return substr_replace($priceHtml, $extraHtml, strpos($priceHtml, $htmlToInsertAfter)+strlen($htmlToInsertAfter),0);
            }
	    }
        return parent::_toHtml();
    }
}
