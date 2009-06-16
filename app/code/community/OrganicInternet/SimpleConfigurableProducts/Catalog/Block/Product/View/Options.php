<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Options
    extends Mage_Catalog_Block_Product_View_Options
{
    /*
    This function is a hack to force at least one 'option' to exist for configurable products
    This forces the rendering of the JS in catalog/product/view/options.phtml which is
    needed by any ajax custom options added by SCP.
    An alternative approach is to copy and paste the whole of that file into the extension
    then remove a single if(), but that file will no doubt change in future core Magento
    versions and maintenance in that case is too much of a headache.
    A second alternative involves loading the js as part of the ajax response but Prototype
    needs to support evalScripts in the global scope for this to work. (Plus you have to deal
    with loading the same JS multiple times)
    Either way it's a bit of a shame that Varien have mixed logic and JS in
    catalog/product/view/options.phtml as it makes it a pain to reuse without
    cut-and-pasting their code.
    */
    public function getOptions()
    {
        $product = $this->getProduct();
        $options = $product->getOptions();
        if ($product->isConfigurable()) {
            $emptyProductOption = new Mage_Catalog_Model_Product_Option();
            $options[] = $emptyProductOption;
        }
        return $options;
    }

    /*
    Don't render the emptyProductOption introduced above.
    */
    public function getOptionHtml(Mage_Catalog_Model_Product_Option $option)
    {
        $type = $option->getType();
        if (empty($type)) {
            return;
        }
        return parent::getOptionHtml($option);
    }
}
