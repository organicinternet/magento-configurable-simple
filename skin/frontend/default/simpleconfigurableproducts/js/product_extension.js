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

    var childProducts = this.config.childProducts;
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
        attributeProducts.push(selected.config.allowedProducts);
    }
    return this.getProductByAttributes(childProductIds, attributeProducts);
}

Product.Config.prototype.getProductIdThatHasLowestPossiblePrice = function(priceType) {
    //Find products which are within consideration based on user's selection of
    //config options so far

    //allowedProducts is a normal numeric array containing product ids.
    //childProducts is a hash keyed on product id
    var childProducts = this.config.childProducts;
    var allowedProducts = [];

    //For each selected config option, get productids still in scope
    for(var s=0, len=this.settings.length-1; s<=len; s++) {
        if (this.settings[s].selectedIndex <= 0){
            break;
        }
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (s==0){
            allowedProducts = selected.config.allowedProducts;
        } else {
            allowedProducts.intersect(selected.config.allowedProducts).uniq();
        }
    }

    //If we can't find any products (because nothing's been selected most likely.
    if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
        //Just use all product ids.
        productIds = Object.keys(childProducts);
    } else {
        productIds = allowedProducts;
    }


    var minPrice = Infinity;
    var lowestPricedProdId = false;

    //Get lowest price from product ids.
    for (var x=0, len=productIds.length; x<len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice < minPrice) {
            minPrice = thisPrice;
            lowestPricedProdId = productIds[x];
        }
    }
    return lowestPricedProdId;
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



Product.OptionsPrice.prototype.updateSpecialPriceDiplay = function(price, finalPrice) {

    var prodForm = $('product_addtocart_form');

    var specialPriceBox = prodForm.select('p.special-price');
    var oldPricePriceBox = prodForm.select('p.old-price, p.was-old-price');
    var magentopriceLabel = prodForm.select('span.price-label');

    if (price == finalPrice) {
        specialPriceBox.each(function(x) {x.hide();});
        magentopriceLabel.each(function(x) {x.hide();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('old-price');
            x.addClassName('was-old-price');
        });
    }else{
        specialPriceBox.each(function(x) {x.show();});
        magentopriceLabel.each(function(x) {x.show();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('was-old-price');
            x.addClassName('old-price');
        });
    }
}

Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    var childProducts = this.config.childProducts;

    if (childProductId){
        var price = childProducts[childProductId]["price"];
        var finalPrice = childProducts[childProductId]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(true);
        optionsPrice.updateSpecialPriceDiplay(price, finalPrice);
        this.updateFormProductId(childProductId);
        this.addParentProductIdToCartForm(this.config.productId);
        this.showCustomOptionsBlock(childProductId, this.config.productId);
    } else {
        var cheapestPid = this.getProductIdThatHasLowestPossiblePrice("finalPrice");
        var price = childProducts[cheapestPid]["price"];
        var finalPrice = childProducts[cheapestPid]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(false);
        optionsPrice.updateSpecialPriceDiplay(price, finalPrice);
        this.showCustomOptionsBlock(false, false);
    }
}


Product.Config.prototype.showCustomOptionsBlock = function(productId, parentId) {
    var coUrl = this.config.ajaxBaseUrl + "co/?id=" + productId + '&pid=' + parentId;
    var prodForm = $('product_addtocart_form');

   if ($('SCPcustomOptionsDiv')==null) {
      return;
   }

    Effect.Fade('SCPcustomOptionsDiv', { duration: 0.5, from: 1, to: 0.5 });
    if(productId) {
        //Uncomment the line below if you want an ajax loader to appear while any custom
        //options are being loaded.
        //$$('span.scp-please-wait').each(function(el) {el.show()});

        //prodForm.getElements().each(function(el) {el.disable()});
        new Ajax.Updater('SCPcustomOptionsDiv', coUrl, {
          method: 'get',
          evalScripts: true,
          onComplete: function() {
              $$('span.scp-please-wait').each(function(el) {el.hide()});
              Effect.Fade('SCPcustomOptionsDiv', { duration: 0.5, from: 0.5, to: 1 });
              //prodForm.getElements().each(function(el) {el.enable()});
          }
        });
    } else {
        $('SCPcustomOptionsDiv').innerHTML = '';
        window.opConfig = new Product.Options([]);
    }
};




Product.OptionsPrice.prototype.reloadPriceLabels = function(productPriceIsKnown) {
    var priceFromLabel = '';
    var prodForm = $('product_addtocart_form');

    if (!productPriceIsKnown && typeof spConfig != "undefined") {
        priceFromLabel = spConfig.config.priceFromLabel;
    }

    var priceSpanId = 'configurable-price-from-' + this.productId;
    var duplicatePriceSpanId = priceSpanId + this.duplicateIdSuffix;

    if ($(priceSpanId) && $(priceSpanId).select('span.configurable-price-from-label')) {
        $(priceSpanId).select('span.configurable-price-from-label').each(function(label) {
            label.innerHTML = priceFromLabel;
        });
    }
    
    if ($(duplicatePriceSpanId) && $(duplicatePriceSpanId).select('span.configurable-price-from-label')) {
        $(duplicatePriceSpanId).select('span.configurable-price-from-label').each(function(label) {
            label.innerHTML = priceFromLabel;
        });
    }
}
