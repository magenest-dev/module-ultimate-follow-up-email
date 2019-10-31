define([
    'jquery',
    'underscore',
    'mage/template',
    'jquery/ui',
    'mage/translate'
], function ($, _, mageTemplate) {
    'use strict';

    /**
     * Check whether the incoming string is not empty or if doesn't consist of spaces.
     *
     * @param {String} value - Value to check.
     * @returns {Boolean}
     */
    function isEmpty(value)
    {
        return (value.length === 0) || (value == null) || /^\s+$/.test(value);
    }

    $.widget('magenest.couponSearch', {
        options: {
            autocomplete: 'off',
            minSearchLength: 2,
            responseFieldElements: 'ul li',
            selectClass: 'selected',
            template:
            '<li class="<%- data.row_class %>" id="qs-option-<%- data.index %>" role="option">' +
            '<span class="qs-option-name">' +
            ' <%- data.title %>' +
            '</span>' +
            '<span aria-hidden="true" class="amount">' +
            '<%- data.num_results %>' +
            '</span>' +
            '</li>',
            submitBtn: 'button[type="submit"]',
            searchLabel: '[data-role=minisearch-label]'
        },
        _create : function () {

        }

    });

    return $.magenest.couponSearch;
});
