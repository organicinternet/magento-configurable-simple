Simple Configurable Products Extension For Magento
==================================================

This extension changes the way that the pricing of configurable products works in Magento.
With this extension enabled, a configurable product's own price is never used. Instead, the price used is that of the appropriate associated child product.

This gives site owners direct control to set the price of every configuration of a product, while still giving users the flexibility they usually get with configurable products.
(There's no more having to set rules such as: +20% for blue, -£10 for small, +15% for leather. You just price the underlying small-blue-leather product at £199.99 and that's what the user pays.)


This change has two effects on the behaviour of a Magento site:

* When an attempt is made to add a configurable product to the basket/cart, the matching underlying simple product is added instead.
* Because the simple products that belong to a configurable product may have different prices, in cases where users haven't yet selected their particular configuration (such as catalog index pages), the site says "Price from:" followed by the lowest price that this product can be configured to. (RSS feeds have been changed in the same way)



Installation
------------

Installation of the extension is the same as for most extensions, that is via your Magento Connect Manager using the extension key found on the MagentoCommerce site.
Once the extension is installed you'll also need to globally set your theme to 'simpleconfigurableproducts'.
To do this, use the Magento admin interface, and under: System->Configuration->Design->Themes set the following:

* Templates = 'simpleconfigurableproducts'
* Skin = 'simpleconfigurableproducts'
* Layout = 'simpleconfigurableproducts'

and if you want to use a custom theme for your site just set:

* Default = 'your custom theme'


You should also refresh your Magento cache and your Layered Navigation Indices. (both under System->Cache Management)


Also, while it is possible to use this extension with the theme applied to just individual products or product categories (rather than globally), make sure you really know what you're doing before you do this.  Read [this forum post](http://www.magentocommerce.com/boards/viewreply/80059/) for more information.



Site Admin Users
----------------

Note that while the name of the configurable product is the one that's shown throughout most of the site, it's the name of the underlying simple product that's shown on the basket/cart/order pages, so you'll need to ensure that your configurable products' associated simple products have meaningful names.


Developers
----------

If you wish to install this code in your local magento installation and still version control with git, create a symlink in your magento webroot which points to the files and directories in the folder you've created for git for this, eg:

    ln -s  /{your git dir}/skin/frontend/default/simpleconfigurableproducts /{your magento root}/skin/frontend/default/simpleconfigurableproducts
    ln -s  /{your git dir}/app/design/frontend/default/simpleconfigurableproducts /{your magento root}/app/design/frontend/default/simpleconfigurableproducts
    ln -s  /{your git dir}/app/code/community/OrganicInternet/SimpleConfigurableProducts /{your magento root}/app/code/community/OrganicInternet/SimpleConfigurableProducts
    ln -s  /{your git dir}/app/etc/modules/OrganicInternet_SimpleConfigurableProducts.xml /{your magento root}/app/etc/modules/OrganicInternet_SimpleConfigurableProducts.xml

(this approach keeps the files needed for this module separate to the rest of the files used by Magento)



Notes
-----

The extension should always use the correct simple product price, including any discounts etc.

### Unsupported Features
* Configurable Product 'Custom Options' will not work. This extension never adds the actual configurable product to the cart, so any custom options associated with configurable products will not be used. (And at the moment neither will custom options added to underlying simple products as they're never shown to the user)

### Feature Aspirations
* Dynamic display of price ranges as product options are selected (for conf products with several options)

### Bugs
* In a few places some English strings are not localised. (RSS feeds only I think)
* From Magento 1.2.0 the product page can be slow for products with many product options. Some Magento Core date calculations are very slow and this extension causes them to be called many times.

###Fixed Bugs
* In v0.5:
* In the cart the images and urls are the same for all simple products which have been added from a configurable product
* The 'Price From' string is removed from all products on a product page, not just the product being configured.
* Price localisation on a per-store basis not working properly - extension uses only global price config



Please report and/or fix bugs [here](http://www.magentocommerce.com/boards/viewchild/11415/)
(Also, please specify which version of Magento you are using when reporting bugs. It's entirely possible that Varien change bits of core code that this extension relies on when they release a new version of Magento. This can sometimes break this extension, though I've tried to minimise the chance of this)
