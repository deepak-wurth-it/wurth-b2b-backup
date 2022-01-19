require([
    'jquery',
    'Amasty_Promo/js/form',
    'uiRegistry',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Ui/js/modal/modal'
], function ($, form, registry, async) {
    var types = [
        'sales_rule_form',
        'salesrulestaging_upcoming_form',
        'salesrulestaging_update_remove_form',
        'salesrulestaging_update_form'
    ];
    async.async({selector: '[data-index="rule_information"]'}, function () {
        $('[data-index="actions"] .fieldset-wrapper-title').click();
        types.map(function (type) {
            form.update(type);
        });
    });

    typesFormUpdate();

    $('body').on(
        {
            'click': function () {
                typesFormUpdate();
            }
        },
        '.schedule-actions a.action-menu-item, #staging_update_new'
    );

    function typesFormUpdate()
    {
        types.map(function (type) {
            formUpdate(type);
        });
    }

    function formUpdate(type)
    {
        async.async('[data-index="simple_action"] select', type + '.' + type + '.' + 'actions', function () {
            form.update(type);
            registry.get(type + '.' + type + '.' + 'actions.simple_action', function (component) {
                component.on('update', function () {
                    form.update(type);
                });
            });
        });
    }
});