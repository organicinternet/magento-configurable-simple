Simple Configurable Products Extension For Magento
--------------------------------------------------

This extension changes the way that the pricing of configurable products works in Magento.
With this extension enabled, a configurable product's own price is never used. Instead, the price used is that of the appropriate associated child product.

This gives users the flexibility they usually get with configurable products, whilst giving site owners direct control to set the price of every configuration of a product.
(No more having to set rules such as: +20% for blue, -£10 for small, +15% for leather. You just set the underlying blue,small,leather product to be £199.99 and that's what the user pays.)


This change has two effects on the behaviour of a Magento site:
* When an attempt is made to add a configurable product to the basket/cart, the matching underlying simple product is added instead.
* Because the simple products belonging to a configurable product may have different prices, in cases where users haven't yet selected their particular configuration (such as catalog index pages), the site says "Price from:" followed by the lowest price that this product can be configured to. (RSS feeds have been changed in the same way)




Site Admin Users
----------------

Once the extension is installed you'll need/want to:
1. Set your theme to 'simpleconfigurableproducts' (to pick up the new javascript)
2. Remove price (and other price-related attributes) from configurable products.  Otherwise it's easy to have your admin users confused about which price will be used.

Notes for 1: if you want to use a custom theme with this extension that's fine too, but you'll have to:
* Use the code from price.phtml in your own theme's price.phtml (the bit that's new as part of this extension is commented)
* Copy product_extension.js into the same folder structure under your own theme.
* Copy simpleconfigurableproducts.xml into the same folder structure under your own theme.

Notes for 2: The extension will still work if you don't do this, but product pricing will become confusing in the admin interface.  To remove the price and similar attributes: Go into 'Manage Attributes' in admin, find the price-related attributes you want to disable for configurable products, then for each set 'Apply to' to be 'selected product types', and then unselect 'configurable product' (use ctrl-click) and save.  You'll then no longer be able to set/see a price for the parent configurable product (it wouldn't be used with this extention anyway)


Also - while the name of the configurable product is the one that's shown throughout most of the site, it's the name of the underlying simple product that's shown on the cart page, so you'll need to ensure that your underlying simple products have meaningful names.


Developers
----------

If you wish to install this extension in your local magento installation and still version control with git, symlink the following files and directories:

    ln -s /your_magento_dir/app/etc/modules/OrganicInternet_SimpleConfigurableProducts.xml app_etc_modules_OrganicInternet_SimpleConfigurableProducts.xml
    ln -s /your_magento_dir/app/design/frontend/default/simpleconfigurableproducts/ ./app_design_frontend_default_simpleconfigurableproducts
    ln -s /your_magento_dir/app/code/community/OrganicInternet/SimpleConfigurableProducts/ ./app_code_community_OrganicInternet_SimpleConfigurableProducts
    ln -s /your_magento_dir/skin/frontend/default/simpleconfigurableproducts/ ./skin_frontend_default_simpleconfigurable_products


Development Feature Requests:

* More admin customisation: Currently this extension works in one way. It doesn't provide any customisation of it's functionality without changing the code.  Also, it's not possible to apply this changed behaviour only to some configurable products; if this extension is installed, all configurable products will have changed behaviour. Some code to provide some flexibility in this are would be great.

* Please report and/or fix bugs!


Notes
-----
The extension should always use the correct simple product price, including any discounts etc.

Currently unsupported:
- Display of the tier pricing tables on configurable product pages.  (Because tier pricing rules could be different for each underlying simple product, so some work would be needed to change the table as users reconfigure their configurable product)
