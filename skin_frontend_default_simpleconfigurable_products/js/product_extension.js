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
    var childProductPrices = [];
    for (var x in childProducts) {
        childProductPrices.push(childProducts[x]);
    }
    return childProductPrices.min();
}


Product.Config.prototype.reloadPriceLabels = function(productPriceIsKnown) {
    var priceLabel = '';
    if (!productPriceIsKnown) {
        priceLabel = 'Price From:';
    }

    //Replace the content of any spans with the css class
    //"configurable-price-from-label"
    $$('span.configurable-price-from-label').each(function(label) {
        label.innerHTML = priceLabel;
    });
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


Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    if (childProductId){
        optionsPrice.productPrice = this.config.childProducts[childProductId];
        optionsPrice.reload();
        this.reloadPriceLabels(true);
        this.updateFormProductId(childProductId);
        this.addParentProductIdToCartForm(this.config.productId);
    } else {
        optionsPrice.productPrice = this.getLowestPossiblePrice();
        optionsPrice.reload();
        this.reloadPriceLabels(false);
    }
}

/****************** ConfigurableToSimple Module - End *************************/
