/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (_, alert, $t) {
    'use strict';

    var defaultConfig = {
        title: $t('Please Select Product'),
        content: $t('Please select at least one product to proceed.'),
        modalClass: 'requisition-popup modal-slide',
        buttons: [{
            text: $t('Ok'),
            'class': 'action primary confirm',

            /** Click action */
            click: function () {
                this.closeModal(true);
            }
        }]
    };

    return function (config) {
        config = config || {};

        return alert(_.extend({}, defaultConfig, config));
    };
});
