define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'madfoat_madfoatpayments',
                component: 'Madfoat_MadfoatPayments/js/view/payment/method-renderer/madfoat-madfoatpayments'
            }
        );
        return Component.extend({});
    }
 );
