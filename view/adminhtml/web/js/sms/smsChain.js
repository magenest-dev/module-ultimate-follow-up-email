/**
 * Created by root on 14/06/2016.
 * @codestandardIgnore
 */
// @codingStandardsIgnoreFile
define([
    "jquery",
    "uiClass",
    'Magento_Ui/js/lib/spinner',
    "underscore"
], function ($, Class,loader, _) {
    "use strict";
    return Class.extend({
        defaults: {
            mainElement:'table[data-role="sms-table"]',
            bodyElement:'tbody[data-role="sms-tbody"]',
            addBtn: 'div[data-role="sms-add"]',
            deleteBtn: '[data-role="sms-del"]',
            templateSelector: 'script[data-role="sms-row-template"]',
            maxId: 1000,
            deleteUrl: 'ultimatefollowupemail/rule/deletesms'

        },
        /**
         * Constructor
         */
        initialize: function (config) {
            this.initConfig(config);
            this.bindAction();
            this.bindDeleteAction();
            return this;
        },

        bindAction: function () {
            var self = this;
            $(self.addBtn).on('click',function () {
                //data-role="sms-row-template"
                var template = $(self.templateSelector).html();

                 self.maxId++;

                var newRow = _.template(template)({id:self.maxId});

                $(self.bodyElement).append(newRow);
                self.bindDeleteAction();
            });
        },

        bindDeleteAction: function() {
            var self = this;
            $(self.deleteBtn).on('click' ,function () {
                var trLi = $(this).closest('tr');
                var isDeleted = trLi.find('input[data-role="delete-input"]');

                if (isDeleted.length > 0) {
                    $(isDeleted[0]).val(1);
                }

                if (trLi.length > 0) {
                    trLi.hide();
                }

            });
        }

    });
});
