/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'jquery'
], function (_, uiRegistry, $) {
    'use strict';

    return function (target) {
        return target.extend({
            initialize: function () {
                this._super();
                if (this.value() == 4) {
                    uiRegistry.get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation').checked(true).hide();
                    uiRegistry.get('sales_rule_form.sales_rule_form.rule_information.coupon_code').hide();
                }
                return this;
            },
            /**
             * Hide fields on coupon tab
             */
            onUpdate: function () {
                this._super();
                if (this.value() == 4) {
                    $('input[name="coupon_code"]').parent().parent().hide();
                    $('input[name="use_auto_generation"]').parent().parent().parent().hide();
                    uiRegistry.get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation').checked(true);
                } else if (this.value() == this.displayOnlyForCouponType) {
                    $('input[name="coupon_code"]').parent().parent().show();
                    $('input[name="use_auto_generation"]').parent().parent().parent().show();
                }
            }
        });
    }
});
