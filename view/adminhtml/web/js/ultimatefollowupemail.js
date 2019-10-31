// @codingStandardsIgnoreFile
define([
    "jquery",
    "uiClass",
    'Magento_Ui/js/lib/spinner',
    "Magenest_UltimateFollowupEmail/js/ultimatefollowupemail",
    "underscore"
], function ($, Class,loader, Fue, _) {
    "use strict";
    return Class.extend({
        defaults: {
            mainElement:'',
            nextButton : 'followup-email-next'
        },
        /**
         * Constructor
         */
        initialize: function (config) {

            this.initConfig(config);
            this.bindAction();
            return this;
        },

        bindAction: function () {
            var self = this;
          /*  this.mainElement.change(function() {
                var redirectUrl = $('option:selected', this).data('redirect-url');
                window.setLocation(redirectUrl);
            }) ;*/

            this.nextButton.click(function () {
                var valid = $("#edit_form").validate().element("#rule_type");
                if (valid) {
                    var redirectUrl = $('[data-action="followup-email-trigger"]').find('option:selected', this).data('redirect-url');
                    window.setLocation(redirectUrl);
                }
            });

        }

    });
});
