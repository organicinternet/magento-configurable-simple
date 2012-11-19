<?php

class OrganicInternet_SimpleConfigurableProducts_Rss_Block_Catalog_Category
    extends Mage_Rss_Block_Catalog_Category
{
    protected function _toHtml()
    {
        $categoryId = $this->getRequest()->getParam('cid');
        $storeId = $this->_getStoreId();
        $rssObj = Mage::getModel('rss/rss');
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category && $category->getId()) {
                $layer = Mage::getSingleton('catalog/layer')->setStore($storeId);
                //want to load all products no matter anchor or not
                $category->setIsAnchor(true);
                $newurl = $category->getUrl();
                $title = $category->getName();
                $data = array('title' => $title,
                        'description' => $title,
                        'link'        => $newurl,
                        'charset'     => 'UTF-8',
                        );
//echo "<pre>";
//print_r($data);
                $rssObj->_addHeader($data);

                $_collection = $category->getCollection();
                $_collection->addAttributeToSelect('url_key')
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('is_anchor')
                    ->addAttributeToFilter('is_active',1)
                    ->addIdFilter($category->getChildren())
                    ->load()
                ;
                $productCollection = Mage::getModel('catalog/product')->getCollection();

                $currentyCateogry = $layer->setCurrentCategory($category);
                $layer->prepareProductCollection($productCollection);
                $productCollection->addCountToCategories($_collection);

                /*if ($_collection->count()) {
                    foreach ($_collection as $_category){
                         $data = array(
                                    'title'         => $_category->getName(),
                                    'link'          => $_category->getCategoryUrl(),
                                    'description'   => $this->helper('rss')->__('Total Products: %s', $_category->getProductCount()),
                                    );

                        $rssObj->_addEntry($data);
                    }
                }
                */
                $category->getProductCollection()->setStoreId($storeId);
                /*
                only load latest 50 products
                */
                $_productCollection = $currentyCateogry
                    ->getProductCollection()
                    ->addAttributeToSort('updated_at','desc')
                    ->setCurPage(1)
                    ->setPageSize(50)
                ;
//echo "<hr>".$_productCollection->getSelect();
                if ($_productCollection->getSize()>0) {
                    foreach ($_productCollection as $_product) {
                        $final_price = $_product->getFinalPrice();
                        $description = '<table><tr>'.
                            '<td><a href="'.$_product->getProductUrl().'"><img src="' . $this->helper('catalog/image')->init($_product, 'thumbnail')->resize(75, 75)
                            .'" border="0" align="left" height="75" width="75"></a></td>'.
                            '<td  style="text-decoration:none;">'.$_product->getDescription().
                            ($_product->isConfigurable() ? '<p> Preis ab: &nbsp; ' : '<p> Preis:').
                            Mage::helper('core')->currency($_product->getPrice()).
                            ($_product->getPrice() != $final_price  ? ' Special Price:'. Mage::helper('core')->currency($final_price) : '').
                            '</p>'.
                            '</td>'.
                            '</tr></table>'
                        ;
                        $data = array(
                                    'title'         => $_product->getName(),
                                    'link'          => $_product->getProductUrl(),
                                    'description'   => $description,
                                    );
//print_r($data);
                        $rssObj->_addEntry($data);
                    }
                }
            }
        }
        return $rssObj->createRssXml();

    }
}

?>
