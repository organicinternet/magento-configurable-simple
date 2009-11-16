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
Once the extension is installed you'll also need to configure your site's theme to pick up the theme files included in this extension.
To do this, use the Magento admin interface, and under: System->Configuration->Design->Themes set the following:

* Templates = 'simpleconfigurableproducts'
* Skin = 'simpleconfigurableproducts'
* Layout = 'simpleconfigurableproducts'

and if you want to use a custom theme for your site just set:

* Default = your custom theme

You should also refresh your Magento cache and your Layered Navigation Indices, etc. (under System->Cache Management)

Note that if you use any per-product or per-category themes you will override these theme settings and SCP will not work as you expect it to.
It is also not recommended to set any per-product or per-category themes to 'simpleconfigurableproducts'



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

### Feature Aspirations
* Static display of price ranges rather than just the 'Price From:' price on catalogue and product pages.
* Dynamic display of price ranges as product options are selected (for conf products with several options)
* Display of offer prices for simple products, see [here:](http://www.magentocommerce.com/boards/viewreply/143350/)
* Change to the simple product image on the product page, if there is one, once all configurable options are selected.
* Indicate that an underlying simple product is only available for backorder (if that's the case) on a conf product page once all options are selected.
* Display 'price from' only if there is more than one price for a product. If all prices are identical, just show 'price' or similar.
* (More of an implementation change/improvement than feature) Make tier price tables load using XHR as pre-generating all tables can be slow for people with 1000's of simple products per configurable product.

### Bugs / Issues
* In a few places some English strings are not localised. (RSS feeds only I think)
* * From Magento 1.2.0 onwards the product page can be slow for products with many related simple products. Amongst other things, some Magento Core date calculations are very slow and this extension causes them to be called many times.
* To check: What's the 'price from' behaviour when all simple products are out of stock?
* If _all_ simple products have a 'required' custom option then no simple products will be used and the configurable product will have no options

### Magento (i.e. not SCP) Bugs/Limitations
* Selecting custom options does not affect the displayed tier price on the product page.
* Discounts which result from Custom Options applying a percentage reduction are only reflected on the cart page, not the product page.

### Fixed Bugs
* Fixed in v0.5:
* In the cart the images and urls are the same for all simple products which have been added from a configurable product
* The 'Price From' string is removed from all products on a product page, not just the product being configured.
* Price localisation on a per-store basis not working properly - extension uses only global price config

* Fixed in v0.6:
* One place where the 'price from' string was not using the correct translate method so wasn't localisable using inline translate.
* Out of stock products were still used to calculate the 'Price From:' lowest price.

Please report and/or fix bugs [here](http://www.magentocommerce.com/boards/viewchild/11415/)
(Also, please specify which version of Magento you are using when reporting bugs. It's entirely possible that Varien change bits of core code that this extension relies on when they release a new version of Magento. This can sometimes break this extension, though I've tried to minimise the chance of this)
