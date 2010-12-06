Simple Configurable Products Extension For Magento
==================================================

*This documentation applies to SCP versions 0.7 onwards.
The documentation for SCP v0.6 and earlier can be seen [here](http://github.com/organicinternet/magento-configurable-simple/blob/34bda60fe4f0ab75d28135748528c08d2e134834/README.markdown)*

This extension changes the way that the pricing of configurable products works in Magento.
With this extension enabled, a configurable product's own price is never used. Instead, the price used is that of the matching associated product.

This gives site owners direct control to set the price of every configuration of a product, while still giving users the flexibility they usually get with configurable products.
(There's no more having to set rules such as: +20% for blue, -£10 for small, +15% for leather. You just price the underlying small-blue-leather product at £199.99 and that's what the user pays.)


This change has two effects on the behaviour of a Magento site:

* When an attempt is made to add a configurable product to the basket/cart, the matching associated simple product is added instead.
* Configurable product prices are shown with "Price from:" followed by the lowest price that this product can be configured to. (Once configurable options have been chosen by the user and the specific product price is known, the 'Price from:' text disappears)



Installation
------------

Installation of SCP is the same as for most extensions, that is via your Magento Connect Manager using the extension key found on the MagentoCommerce site.
Important: Once installed you must refresh all caches and reindex all data (under System->Cache Management and System->Index Management). You will then also need to logout then login of Admin (the SCP Admin options will not be displayed otherwise).

There are also some SCP configuration options under System->Configuration->SCP Config, and it's likely you will want to change these from their default values. What each option does should hopefully be self-explanatory.



Uninstallation
------------
Uninstallation of SCP is the same as for most extensions, that is via your Magento Connect Manager.
Once SCP is uninstalled you must go into Admin and refresh cache and reindex all data (under System->Cache Management and System->Index Management)



Some key things to be aware of
------------------------------
* SCP does not allow you to have some configurable products using the SCP logic and some others using the default Magento logic on the same site. If SCP is installed all configurable products will use SCP logic.
* Do not assign any custom options, tier prices, or apply price rules directly to the Configurable Product when SCP is installed. Although SCP will not use them if you do (as SCP only adds the associated products to the cart), they may well still be displayed in various places by the core Magento code and so can be very confusing for your customers. In addition, SCP is not tested for these cases so it's possible that you'll see odd behaviour or errors. If you assign options/prices/rules directly to the associated simple products instead then they'll work just fine.


Main Features
-------------

* SCP fully supports special prices, catalog price rules, tier prices, custom options etc.
* In addition it can optionally change the product's image, associated image gallery, name and description to match the associated product when a user has made their selection of a product's configurable options. (so if a user has chosen a silver phone they can see it in silver before they buy it)
* There's the option to show whether the configurable product or associated product name and image are shown in the cart
* There's the option to show price ranges for the remaining choices in the configurable product option drop downs on the product page
* It now uses the new Magento 1.4 indexers to perform most calculations behind the scenes, so doesn't slow down your site.
* There's no theme setup needed. Just install the extension like any other, refresh your caches, and away you go.



Functionality in detail
-----------------------
In Magento a Configurable Product has one or several Associated Products. These Associated Products are just Simple Products that you've chosen to 'associate' with the Configurable Product.
A Configurable Product also has a set of Configurable Options (say Colour, Size, Material), and a combination of each of these options maps onto a specific Associated Product.
For example, if the configurable options are {Colour, Size, Material} the choices {red, small, steel} will map onto one associated simple product, and the choices {orange, medium, plastic} will map onto another, etc.

With vanilla Magento, when working with configurable products any pricing that has been directly assigned to the associated simple products in the Magento admin interface is ignored. The configurable product price is actually calculated from the price assigned directly to the configurable product itself, plus any modifiers that can be set per configurable option. (eg +10% for green, +€99 for titanium, etc)

SCP's original goal was to allow site owners to have more direct control over product pricing by changing the way pricing for Magento Configurable Products work, such that the usual rules for Configurable Product pricing (described above) are ignored, and the price that's directly assigned to the associated simple products is used instead. The mechanism SCP uses to achieve this is actually to add the simple product to the cart, instead of adding the configurable product. This approach has a number of benefits, and some limitations.

The main benefit is that it allows site owners to not only directly choose the price that each combination of configurable options will result in, but it also allows them to assign completely different tier prices, custom options, special prices (aka offer prices) etc on the same basis.  So for example if the products in question are tables, a {small, oak} table could have a 'buy 2 for only £129.99 each' offer, whereas the {large, pine} version of the same table may have a Custom Option which allows the customer to specify what kind of finish or dye they require, or have a 10% discount this week. At the time of writing this kind of flexibility is not present with standard Magento Configurable Products.

The main downside to this flexibility is only when you don't need it. If you just want flexibility around pricing, but want to have the same set of custom options, discounts, etc for every associated product there currently isn't an easy way to do this with SCP. At the moment you have to manually assign the same values to each associated product. It's something I'll look into handling better in future versions of SCP.


Notes
-----

v0.7 of SCP is a significant rewrite. Magento 1.3 and earlier are no longer supported.

* Magento doesn't normally allow Simple Products which have compulsory Custom Options to be associated to a configurable product, as Magento isn't normally able to display these Custom Option to the user. (so it could never be selected despite being compulsory)
SCP *does* allow this association, as it is able to show these custom options to the user. However, if you uninstall SCP then later save any Configurable Products that have associated products that have compulsory Custom Options they'll no longer be associated to the Configurable Product and will need re-associating if you later install SCP.  (without SCP installed you can't re-associate them while there are still compulsory custom options on the simple product)

* SCP uses a JavaScript file called scp_product_extension.js. This needs to be loaded after the Magento product.js file, and SCP is written such that it will be. However in some cases the new Magento 'Merge JavaScript Files' option may cause it to be loaded earlier, which will stop SCP from working. If you are seeing JavaScript errors, or if you are seeing the Configurable Product being added to the cart instead of the Associated Products, turn off the 'Merge JavaScript Files' option in Admin->Configuration->Developer

* Some of SCP's JavaScript is dependent on the DOM structure of the Product Page (just as the core Magento product.js is). If you have a very heavily customised theme you may find you have JavaScript errors and may need to modify some of the JS in product_extension.js to match your modified theme.



## Feature Aspirations
* Investigate whether it's possible to allow custom options to be set on the Configurable Product (for when they need to be the same across all associated products).
* Backordering enhancements. Currently only in-stock associated products are shown even if allow backordering is enabled. This is inline with default Magento behaviour, but it's something that could possibly be enhanced by SCP.
* Possibly allow SCP logic and Magento logic for Configurable Options to run side-by-side.


## Lightboxes

If you're using a 3rd party lightbox to display your product images rather than the built-in Magento one, it is likely this will not work with SCP without some additional work on your part. This is not an SCP bug as such; it's not possible to SCP to be compatible with all the possible 3rd party extensions.
To fix, it's often just a matter of editing the showFullImageDiv function in the scp_product_extension.js file: Change evalScripts to true if it's not already, and possibly you'll also need to remove the code which exists in a few places which looks like:
  product_zoom = new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint');
Depending on your choice of lightbox it may be much more complex than this, but it's very likely that it's this function that you'll have to update to support your Lightbox.


## Bugs / Issues

#### Open Bugs
* See [here](http://github.com/organicinternet/magento-configurable-simple/issues)
* When SCP dynamically upates various parts of the product page (description, attributes, product name etc) for a matching associated product, it only works if the configurable product also has the same property present. So for example, if you have no description on your configurable product, but you do on one of your associated products, it will not be displayed when this associated product is selected by the user's choice of configurable options. This is because if there's not already a description on the page, SCP doesn't know which part of the page to update to show the associated product description. (or name, or extended attributes, etc)

#### Magento (i.e. not SCP) Bugs/Limitations
* Selecting custom options does not affect the displayed tier price on the product page.

#### Reporting Bugs
Please report and/or fix bugs [here](http://github.com/organicinternet/magento-configurable-simple/issues)

When reporting bugs, please specify:

* How to reproduce it, in as much detail as you can
* Which version of Magento you are using
* Which version of SCP you are using
* Whether you're seeing any JavaScript errors, what they are and when they occur (install firebug for firefox, and look at the script console)
* Whether there are any errors output to your error log (turn it on in admin) or output into the page's html.
* Which other extensions you have installed
* If possible, whether the problem goes away when SCP is uninstalled

