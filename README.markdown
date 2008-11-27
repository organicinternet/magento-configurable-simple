Simple Configurable Products Extension For Magento
==================================================

This extension changes the way that the pricing of configurable products works in Magento.
With this extension enabled, a configurable product's own price is never used. Instead, the price used is that of the appropriate associated child product.

This gives site owners direct control to set the price of every configuration of a product, while still giving users the flexibility they usually get with configurable products.
(There's no more having to set rules such as: +20% for blue, -£10 for small, +15% for leather. You just price the underlying small-blue-leather product at £199.99 and that's what the user pays.)


This change has two effects on the behaviour of a Magento site:
* When an attempt is made to add a configurable product to the basket/cart, the matching underlying simple product is added instead.
* Because the simple products that belong to a configurable product may have different prices, in cases where users haven't yet selected their particular configuration (such as catalog index pages), the site says "Price from:" followed by the lowest price that this product can be configured to. (RSS feeds have been changed in the same way)




Site Admin Users
----------------

Once the extension is installed you'll need/want to:
1. Set your theme to 'simpleconfigurableproducts'
2. Remove price (and other price-related attributes) from configurable products. If you do not do this your admin users may get confused about which price will be used.



Notes for 1:
It is recommended that you change your theme globally (In admin: System->Configuration->Design->Themes->Default = 'simpleconfigurableproducts')
It is possible to use this extension with the theme applied to just product or product categories, but make sure you really know what you're doing before you do this.
Read [this forum post](http://www.magentocommerce.com/boards/viewreply/80059/) for more information.

If you want to use a custom theme with this extension that's fine too, but you'll have to:
* Use the code from `price.phtml` in your own theme's `price.phtml` (the bit that's new as part of this extension is commented)
* Copy `product_extension.js` into the same folder structure under your own theme.
* Copy `simpleconfigurableproducts.xml` into the same folder structure under your own theme.


Notes for 2: The extension will still work if you don't do this, but product pricing may be a bit confusing in the admin interface.

To remove the price and similar attributes: Go into 'Manage Attributes' in admin, find the price-related attributes you want to disable for configurable products, then for each set 'Apply to' to be 'selected product types', and then unselect 'configurable product' (use ctrl-click) and save.  You'll then no longer be able to set/see a price for the parent configurable product (it wouldn't be used with this extention anyway)


Also, while the name of the configurable product is the one that's shown throughout most of the site, it's the name of the underlying simple product that's shown on the basket/cart/order pages, so you'll need to ensure that your underlying simple products have meaningful names.


Developers
----------

If you wish to install this code in your local magento installation and still version control with git, create a symlink in your magento webroot which points to the files and directories in the folder you've created for git for this, eg:

    ln -s  /{your git dir}/skin/frontend/default/simpleconfigurableproducts /{your magento root}/skin/frontend/default/simpleconfigurableproducts
    ln -s  /{your git dir}/app/design/frontend/default/simpleconfigurableproducts /{your magento root}/app/design/frontend/default/simpleconfigurableproducts
    ln -s  /{your git dir}/app/code/community/OrganicInternet/SimpleConfigurableProducts /{your magento root}/app/code/community/OrganicInternet/SimpleConfigurableProducts
    ln -s  /{your git dir}/app/etc/modules/OrganicInternet_SimpleConfigurableProducts.xml /{your magento root}/app/etc/modules/OrganicInternet_SimpleConfigurableProducts.xml

(this approach keeps the files needed for this module separate to the rest of the files used by Magento)


### Requests for Further Development

* More admin customisation: Currently this extension works in one way. It doesn't provide any customisation of it's functionality without changing the code. (Specifically options to change which image and link are used (i.e. the ones from parent configurable product or the ones from the child simple product) when the item is added the basket.
* Display of tier pricing tables
* A price range which changes as subsequent product options are picked


Notes
-----
The extension should always use the correct simple product price, including any discounts etc.

Features currently unsupported:
* Display of the tier pricing tables on configurable product pages.  (Because tier pricing rules could be different for each underlying simple product, some work would be needed to change the table as users reconfigure their configurable product)

Bugs
-----
Please report and/or fix bugs [here](http://www.magentocommerce.com/boards/viewchild/11415/)
(Also, please specify which version of Magento you are using when reporting bugs. It's entirely possible that Varien change bits of core code that this extension relies on when they release a new version of Magento. This can sometimes break this extension, though I've tried to minimise the chance of this)
