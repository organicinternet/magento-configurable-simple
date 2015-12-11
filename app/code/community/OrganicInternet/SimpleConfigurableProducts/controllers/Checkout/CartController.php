<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class OrganicInternet_SimpleConfigurableProducts_Checkout_CartController extends Mage_Checkout_CartController
{
	public function configureAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
		$quoteItem = null;
		$cart = $this->_getCart();

		if ($id)
		{
			$quoteItem = $cart->getQuote()->getItemById($id);
		}

		if (!$quoteItem)
		{
			$this->_getSession()->addError($this->__('Quote item is not found.'));
			$this->_redirect('checkout/cart');

			return;
		}

		try
		{
			$params = new Varien_Object();
			$params->setCategoryId(false);
			$params->setConfigureMode(true);
			$params->setBuyRequest($quoteItem->getBuyRequest());

			$id = $quoteItem->getProduct()->getId();

			$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id);

			if (is_array($parentIds) && count($parentIds))
			{
				$id = current($parentIds);
			}

			Mage::helper('catalog/product_view')->prepareAndRender($id, $this, $params);
		}

		catch (Exception $e)
		{
			$this->_getSession()->addError($this->__('Cannot configure product.'));

			Mage::logException($e);

			$this->_goBack();

			return;
		}
	}
}
