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
                type: 'netcents_merchant',
                component: 'NetCents_Merchant/js/view/payment/method-renderer/netcents-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);