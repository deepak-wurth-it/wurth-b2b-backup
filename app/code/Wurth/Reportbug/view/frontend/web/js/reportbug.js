define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, Component, modal, urlBuilder) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            this.openReportBugModel();
        },
        openReportBugModel: function () {
            let self = this;
            $(document).on('click', '.itembug', function() {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: 'report-a-bug',
                    title: $.mage.__('Report Bug'),
                    buttons: [
                        {
                            text: $.mage.__('Send'),
                            class: 'primary action',
                            click: function () {
                                if(self.is_logged_in == "true"){
                                    self.sendReportBug();
                                }else{
                                    self.askLoginModel();
                                }
                            }
                        },
                        {
                            text: $.mage.__('Close'),
                            class: 'primary action',
                            click: function () {
                                this.closeModal();
                            }
                        }
                    ]
                };

                modal(options, $('#report_bug_model'));
                $('#report_bug_model').modal('openModal');
            });
        },
        sendReportBug: function () {
            let url = urlBuilder.build("reportbug/index/sendreportbug");
            $.ajax({
                url: url,
                type: "POST",
                data: $('#report_bug_form').serialize(),
                showLoader: true,
            }).success(function (response) {
               $('#report_bug_model').modal("closeModal");
            });
        },
        askLoginModel: function () {
            $('#report_bug_model').modal("closeModal");
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: false
            };

            modal(options, $('#ask_login_model'));
            $('#ask_login_model').modal('openModal');
        }
    });
});
