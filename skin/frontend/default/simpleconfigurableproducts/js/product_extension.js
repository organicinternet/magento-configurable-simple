/*
Some of these override earlier varien/product.js methods, therefore
varien/product.js must have been included prior to this file.
*/


//Helper function which works out which productId all passed attributes match.
//Assumes array(productIds), array(array(productIds))
Product.Config.prototype.getProductByAttributes = function(productIds, attributes){
    for (var i=0;i<productIds.length;i++) {
        var foundMatchingProduct = true;
        for (var a=0;a<attributes.length;a++) {
            if (attributes[a].indexOf(productIds[i]) == -1) {
                foundMatchingProduct = false;
                break;
            }
        }
        if (foundMatchingProduct) {
            return productIds[i];
        }
    }
    return false;
}

//Determines which simple product the currently selected configurable attributes
//map to
Product.Config.prototype.getMatchingSimpleProduct = function(){

    var childProducts =  this.config.childProducts;
    var childProductIds = [];
    for (var x in childProducts) {
        childProductIds.push(x);
    }

    var attributeProducts = [];
    for(var s=this.settings.length-1;s>=0;s--){
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (!selected.config){
            return false;
        }
        attributeProducts.push(selected.config.products);
    }
    return this.getProductByAttributes(childProductIds, attributeProducts);
}


Product.Config.prototype.getLowestPossiblePrice = function() {
    var childProducts =  this.config.childProducts;
    var minPrice = Infinity;
    var minPriceString = "";
    //Be careful here to return the exact input price value,
    //not some (possibly badly) converted version
    for (var x in childProducts) {
        var thisPrice = Number(childProducts[x]);
        if (thisPrice < minPrice) {
            minPrice = thisPrice;
            minPriceString = childProducts[x];
        }
    }
    return minPriceString;
}


Product.Config.prototype.updateFormProductId = function(productId){
    if (!productId) {
        return false;
    }
    var currentAction = $('product_addtocart_form').action;
    newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + productId + '/');
    $('product_addtocart_form').action = newcurrentAction;
    $('product_addtocart_form').product.value = productId;
}


Product.Config.prototype.addParentProductIdToCartForm = function(parentProductId) {
    if (typeof $('product_addtocart_form').cpid != 'undefined') {
        return; //don't create it if we have one..
    }
    var el = document.createElement("input");
    el.type = "hidden";
    el.name = "cpid";
    el.value = parentProductId.toString();
    $('product_addtocart_form').appendChild(el);
}


Product.Config.prototype.showTierPricesBlock = function(productId) {
    config = this.config;
    $$('ul.product-pricing').each(function(label) {
        label.remove();
    });

    if (productId && config.childProductTierPriceHtml[productId]) {
        $$('div.product-options-bottom').each(function(label) {
            label.innerHTML = this.config.childProductTierPriceHtml[productId] + label.innerHTML;
        });
    }
}


Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    if (childProductId){
        optionsPrice.productPrice = this.config.childProducts[childProductId];
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(true);
        this.updateFormProductId(childProductId);
        this.addParentProductIdToCartForm(this.config.productId);
        this.showTierPricesBlock(childProductId);
    } else {
        optionsPrice.productPrice = this.getLowestPossiblePrice();
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(false);
        this.showTierPricesBlock(false);
    }
}




Product.OptionsPrice.prototype.reloadPriceLabels = function(productPriceIsKnown) {
    var priceLabel = '';
    if (!productPriceIsKnown) {
        priceLabel = spConfig.config.priceFromLabel;
    }

    var priceSpanId = 'configurable-price-from-' + this.productId;
    var duplicatePriceSpanId = priceSpanId + this.duplicateIdSuffix;

    $(priceSpanId).select('span.configurable-price-from-label').each(function(label) {
        label.innerHTML = priceLabel;
    });

    if ($(duplicatePriceSpanId) && $(duplicatePriceSpanId).select('span.configurable-price-from-label')) {
        $(duplicatePriceSpanId).select('span.configurable-price-from-label').each(function(label) {
            label.innerHTML = priceLabel;
        });
    }
}

/****************** ConfigurableToSimple Module - End *************************/
