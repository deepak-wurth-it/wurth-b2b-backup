/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['Magento_Ui/js/modal/modal-component'], function (ModalComponent) {
    'use strict';

    return ModalComponent.extend({
        /**
         * Init toolbar section so other components will be able to place something in it
         */
        initToolbarSection: function () {
            this.set('toolbarSection', this.modal.data('mageModal').modal.find('header').get(0));
        }
    });
});
