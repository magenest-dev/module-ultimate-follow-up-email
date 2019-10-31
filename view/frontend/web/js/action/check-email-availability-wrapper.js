define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'mage/utils/wrapper'
], function($, urlBuilder, quote, wrapper){

    return function (checkEmailAvailability) {
        return wrapper.wrap(checkEmailAvailability, function(originalFunction, deferred, email) {
            $.post(
                urlBuilder.build('ultimatefollowupemail/capture/guest') ,
                {
                    email: email,
                    quote_id:quote.getQuoteId()
                }
            );
            return originalFunction();
        });
    }
});