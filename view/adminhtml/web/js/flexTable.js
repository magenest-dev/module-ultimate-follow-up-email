// @codingStandardsIgnoreFile
define([
    "jquery",
    "uiClass",
    'Magento_Ui/js/lib/spinner',
    "Magento_Ui/js/modal/modal",
    "underscore"
], function ($, Class, loader, modal, _) {
    "use strict";

    return Class.extend({
        defaults: {
            /**
             * Initialized solutions
             */
            table: '',
            url: '',
            getEmailTemplateUrl: '',
            activeSelectorEmail: ''
        },
        /**
         * Constructor
         */
        initialize: function (config) {

            var self = this;

            this.initConfig(config);

            this.bindAction();
            this._addButton(config);

            return this;
        },

        updateEmailTemplate: function () {
            var self = this;
            var newEmailId = null;
            $.ajax({
                url: self.getEmailTemplateUrl,
                async: false
            }).done(function (data) {
                $('select[data-role="followup-email-template"]').each(function () {
                    var options = [];
                    var existed = false;
                    $(this).find('option').each(function () {
                        options.push({
                            value: this.value,
                            label: this.label,
                            self: $(this)
                        });
                    });
                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            existed = false;
                            for (var j = 0; j < options.length; j++) {
                                if (options[j]['value'] && options[j]['value'] == data[i]['id']) {
                                    if (options[j]['label'] != data[i]['label']) {
                                        options[j]['self'].text(data[i]['label']);
                                    }
                                    existed = true;
                                    break;
                                }
                            }
                            if (!existed) {
                                $('<option value="' + data[i]['id'] + '">' + data[i]['label'] + '</option>').insertAfter($(this).find('.email_separator'));
                                newEmailId = data[i]['id'];
                            }
                        }
                    }
                });
            });
            return newEmailId;
        },

        bindAction: function () {
            var self = this;
            var previouslySelected;
            $('[data-action="delete-row"]').click(function () {
                $(this).parent().parent().remove();
            });
            $('select[data-role="followup-email-template"]').focus(function(){
                if (this.value) {
                    previouslySelected = this.value;
                }
            }).change(function () {
                return false;
                self.activeSelectorEmail = this;
                var optionSelected = $(this).find("option:selected");
                var role = optionSelected.data('action');
                if (this.selectedIndex == 1) {
                    $('[data-role="wrapper-modal-new-email"]').find('iframe').attr( 'src', function ( i, val ) { return val; });
                    this.modal = $('[data-role="wrapper-modal-new-email"]').modal({
                        modalClass: 'modal-followup-email-slide',
                        type: 'slide',
                        transitionEvent: false,
                        trigger: ['closed'],
                        buttons: [],
                        closed: function () {
                            var newEmailId = self.updateEmailTemplate();
                            $(self.activeSelectorEmail).val(previouslySelected);
                            if (newEmailId) {
                                $(self.activeSelectorEmail).val(newEmailId);
                            }
                        }
                    });

                    this.modal.modal('openModal');
                }

                if (this.selectedIndex == 2) {
                    $('[data-role="wrapper-modal-edit-email"]').find('iframe').attr( 'src', function ( i, val ) { return val; });
                    this.modal = $('[data-role="wrapper-modal-edit-email"]').modal({
                        modalClass: 'modal-followup-email-slide',
                        type: 'slide',
                        transitionEvent: false,
                        trigger: ['closed'],
                        buttons: [],
                        closed: function () {
                            $(self.activeSelectorEmail).val(previouslySelected);
                            self.updateEmailTemplate();
                        }
                    });

                    this.modal.modal('openModal');
                }
            });

            //make the required input
            self.table.find('[data-role="require-anchor"]').not('[data-sample="email-chain"]').addClass('required-entry').addClass('_required');
            self.table.find('[data-require="required-entry"]').not('[data-sample="email-chain"]').addClass('required-entry').addClass('_required');

            self.table.find('[data-sample="email-chain"]').removeClass('required-entry').removeClass('_required');
        },
        escapeRegExp: function (string) {
            return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        },

        replaceAll: function (string, find, replace) {
            var pin = this;
            return string.replace(new RegExp(pin.escapeRegExp(find), 'g'), replace);
        },

        _addButton: function (config) {
            var self = this;

            var table = self.table.first();
            var addBtn = table.find('[data-role="add-new-row"]');

            addBtn.click(function () {

                var rowIds = new Array();
                var template = self.table.find('[data-role="row-pattern"]').html();

                var trElements = self.table.find('tbody').find('tr');

                jQuery(trElements).each(function (index, element) {
                    if (jQuery(element).data('order') != null) {
                        rowIds.push(jQuery(element).data('order'));
                    }
                });


                var row_id = Math.max.apply(rowIds, rowIds);
                var next_id = parseInt(row_id) + 1;


                var templateRow = '<tr ' + ' data-order =' + next_id + '>' + template;
                var valueFind = '/value=\".+\"/';
                templateRow = self.replaceAll(templateRow, valueFind, ' ');

                var find = "[1000]";
                var re = new RegExp(find, 'g');

                var replace = "[" + next_id + "]";

                var res = self.replaceAll(templateRow, find, replace);

                find = '_1000_';
                replace = '_' + next_id + '_';
                re = new RegExp(find, 'g');
                res = self.replaceAll(res, find, replace);

                var newRow = res + '</tr>';


                var appendRow = jQuery(newRow);

                table.find('tbody').append(newRow);

                self.bindAction();

            });
        }


    });
});
