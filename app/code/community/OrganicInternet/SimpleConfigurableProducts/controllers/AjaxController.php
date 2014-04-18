<?php
require_once 'Mage/Catalog/controllers/ProductController.php';

class OrganicInternet_SimpleConfigurableProducts_AjaxController extends Mage_Catalog_ProductController
{
    public function coAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           $this->loadLayout(false);
           $this->renderLayout();
       }
    }

    public function imageAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           $this->loadLayout(false);
           $this->renderLayout();
       }
    }

    public function galleryAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           #$this->_initProductLayout($product);
           $this->loadLayout();
           $this->renderLayout();
       }
    }

    //Copy of parent _initProduct but changes visibility checks.
    //Reproducing functionality like this is far from great for future compatibilty
    //but at the moment I don't see a better alternative.
    protected function _initProduct()
    {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $parentId   = (int) $this->getRequest()->getParam('pid');

        if (!$productId || !$parentId) {
            return false;
        }

        $parent = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($parentId);
        if (!$parent->getId()) {
            return false;
        }

        $childIds = $parent->getTypeInstance()->getUsedProductIds();
        if (!is_array($childIds) || !in_array($productId, $childIds)) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        // @var $product Mage_Catalog_Model_Product
        if (!$product->getId()) {
            return false;
        }
        
        if (!Mage::helper('catalog/product')->canShow($parent) && !Mage::helper('catalog/product')->canShow($product)) {
            return false;
        }
        
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }
        $product->setCpid($parentId);
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        return $product;
    }
}
