define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, placeOrderAction, selectPaymentMethodAction, customer, checkoutData, additionalValidators) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Madfoat_MadfoatPayments/payment/madfoat'
            },
            initialize: function() {
                this._super();
                window.madfoatloaded = false;
                console.log("Loading");
                console.log(checkoutData);
                console.log(window.checkoutConfig.payment.madfoat_madfoatpayments);
                if(window.checkoutConfig.payment.madfoat_madfoatpayments.frameMode == 'yes'){
                    window.madfoatloadedInt = setInterval(function(){
                        if(!window.madfoatloaded){
                            if($("#payment_method_madfoat .payment-method-content").length == 1){
                                var store_id = window.checkoutConfig.payment.madfoat_madfoatpayments.storeId;
                                var currency = window.checkoutConfig.quoteData.quote_currency_code;
                                var test_mode = window.checkoutConfig.payment.madfoat_madfoatpayments.testMode;
                                var saved_cards = window.checkoutConfig.payment.madfoat_madfoatpayments.savedCards;
								var language = window.checkoutConfig.payment.madfoat_madfoatpayments.language;

                                var madfoatMessage = {
                                    "message_id": "init_madfoat_config",
                                    "store_id": store_id,
                                    "currency": currency,
                                    "test_mode": test_mode,
                                    "saved_cards": saved_cards
                                }

                                var initMessage = JSON.stringify(madfoatMessage);

                                var frameHeight = 320;
                                if(saved_cards.length > 0){
                                    frameHeight += 30;
                                    frameHeight += (saved_cards.length * 110);
                                }

                                //var iframeUrl = "https://secure.madfoat.com/jssdk/v2/token_frame.html?token=" + Math.floor((Math.random() * 9999999999) + 1);
								var iframeUrl = "https://uat-secure.madfoat.dev/jssdk/v2/token_frame.html?token=" + Math.floor((Math.random() * 9999999999) + 1) + "&lang=" + language;
;
                                var iframeHtml = ' <iframe id="madfoat_iframe" src= "' + iframeUrl + '" style="width: 100%; height: ' + frameHeight + 'px; border: 0;margin-top: 20px;" sandbox="allow-forms allow-modals allow-popups-to-escape-sandbox allow-popups allow-scripts allow-top-navigation allow-same-origin"></iframe>';
                                $("#payment_method_madfoat .payment-method-content").prepend(iframeHtml);
                                window.madfoatloaded = true;
                                clearInterval(window.madfoatloadedInt);

                                setTimeout(function(){
                                    document.getElementById('madfoat_iframe').contentWindow.postMessage(initMessage,"*");
                                }, 1500);

                                if (typeof window.addEventListener != 'undefined') {
                                    window.addEventListener('message', function(e) {
                                        var message = e.data;
                                         if(message != ""){
                                            var isJson = true;
                                            try {
                                                JSON.parse(str);
                                            } catch (e) {
                                                isJson = false;
                                            }
                                            if(isJson || (typeof message === 'object' && message !== null)){
                                                var madfoatMessage = (typeof message === 'object') ? message : JSON.parse(message);
                                                if(madfoatMessage.message_id != undefined){
                                                    switch(madfoatMessage.message_id){
                                                        case "return_madfoat_token": 
                                                            var payment_token = madfoatMessage.payment_token;
                                                            if(payment_token != ""){
                                                                console.log("Madfoat Token Received: " + payment_token);
                                                                $("#madfoat_payment_token").val(payment_token);
                                                            }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        
                                    }, false);
                                    
                                } else if (typeof window.attachEvent != 'undefined') { // this part is for IE8
                                    window.attachEvent('onmessage', function(e) {
                                        var message = e.data;
                                         if(message != ""){
                                             try {
                                                JSON.parse(str);
                                            } catch (e) {
                                                isJson = false;
                                            }
                                            if(isJson || (typeof message === 'object' && message !== null)){
                                                var madfoatMessage = (typeof message === 'object') ? message : JSON.parse(message);
                                                if(madfoatMessage.message_id != undefined){
                                                    switch(madfoatMessage.message_id){
                                                        case "return_madfoat_token": 
                                                            var payment_token = madfoatMessage.payment_token;
                                                            if(payment_token != ""){
                                                                console.log("Madfoat Token Received: " + payment_token);
                                                                $("#madfoat_payment_token").val(payment_token);
                                                            }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        
                                    });
                                }
                            }
                        }
                    }, 1000);
                }
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate() && ($("#madfoat_payment_token").val() != '' || window.checkoutConfig.payment.madfoat_madfoatpayments.frameMode != 'yes')) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));

                    return true;
                }
                return false;
            },
            getData: function () {
                var saveCard = 'no';
                if($("#payment_method_madfoat .save-card-option input").length == 1){
                    if($("#payment_method_madfoat .save-card-option input").is(":checked")){
                        saveCard = 'yes';
                    }
                }
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_token': $("#madfoat_payment_token").val(),
                        'save_card': saveCard
                    }
                }
                console.log(data);
                return data;
            },
            afterPlaceOrder: function () {
                if(window.checkoutConfig.payment.madfoat_madfoatpayments.frameMode == 'yes' && false){
                    $.ajax({
                        url : window.checkoutConfig.payment.madfoat_madfoatpayments.iframeUrl,
                        type: "GET",
                        success: function (iframeUrl) {
                            var iframeHtml = ' <iframe id="madfoat" src= "' + iframeUrl + '" style="width: 100%; height: 450px; border: 0;" sandbox="allow-forms allow-modals allow-popups-to-escape-sandbox allow-popups allow-scripts allow-top-navigation allow-same-origin"></iframe>';
                            $(".payment-method._active .payment-method-content").html(iframeHtml);
                        },
                        error: function (response) {
                        }
                    });
                }else{
                    $.mage.redirect(window.checkoutConfig.payment.madfoat_madfoatpayments.redirectUrl);
                }
            },
            isVaultEnabled: function () {
                    return true;
            },
            isLoggedIn: function () {
                    return customer.isLoggedIn();
            },
            CreateGuid: function (){  
               function _p8(s) {  
                  var p = (Math.random().toString(16)+"000000000").substr(2,8);  
                  return s ? "-" + p.substr(0,4) + "-" + p.substr(4,4) : p ;  
               }  
               return _p8() + _p8(true) + _p8(true) + _p8();  
            }
        });
    }
);
