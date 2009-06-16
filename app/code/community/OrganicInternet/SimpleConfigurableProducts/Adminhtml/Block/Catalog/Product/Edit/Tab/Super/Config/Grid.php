<?php

class OrganicInternet_SimpleConfigurableProducts_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
{
    #Copied from Magento v1.3.1 code.
    #Only need to comment out addFilterByRequiredOptions but there's no
    #nice way of doing that without cutting and pasting the method into my own
    #derived class. Boo.
    #Have also replaced parent::_prepareCollection with Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    protected function _prepareCollection()
    {
        $allowProductTypes = array();
        foreach (Mage::getConfig()->getNode('global/catalog/product/type/configurable/allow_product_types')->children() as $type) {
            $allowProductTypes[] = $type->getName();
        }

        $product = $this->_getProduct();
        $collection = $product->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price')
            ->addFieldToFilter('attribute_set_id',$product->getAttributeSetId())
            ->addFieldToFilter('type_id', $allowProductTypes);
            //->addFilterByRequiredOptions();

        Mage::getModel('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($collection);

        foreach ($product->getTypeInstance(true)->getUsedProductAttributes($product) as $attribute) {
            $collection->addAttributeToSelect($attribute->getAttributeCode());
            $collection->addAttributeToFilter($attribute->getAttributeCode(), array('nin'=>array(null)));
        }

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        return $this;
    }
}
