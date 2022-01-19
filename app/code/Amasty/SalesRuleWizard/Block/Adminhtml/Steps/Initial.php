<?php

namespace Amasty\SalesRuleWizard\Block\Adminhtml\Steps;

class Initial extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    const TYPE_FREE_GIFT = 'add_free_product';

    const SCENARIO_BUY_X_GET_Y = 'buy_x_get_y';
    const SCENARIO_SPENT_X_GET_Y = 'spent_x_get_y';
    /**
     * @return string
     */
    public function getSelectedType()
    {
        return 'add_free_product';
    }

    /**
     * @return string
     */
    public function getSelectedScenario()
    {
        return self::SCENARIO_BUY_X_GET_Y;
    }

    /**
     * The first is wizard registration name, the rest is steps registration name
     *
     * @return string
     */
    public function getAllComponentsNames()
    {
        /** @var array $steps */
        $steps = $this->getParentBlock()->getStepComponents();
        array_unshift($steps, $this->getParentComponentName());
        $jsString = '\'' . implode('\', \'', $steps) . '\'';

        return $jsString;
    }

    /**
     * @return string
     */
    public function getRuleScenariosJson()
    {
        $actions = [
            self::TYPE_FREE_GIFT => [
                'label' => __('Automatically add products to cart'),
                'scenarios' => $this->getFreeGiftScenarios()
            ]
        ];

        return \Zend_Json::encode($actions);
    }

    /**
     * @return array
     */
    protected function getFreeGiftScenarios()
    {
        return [
            [
                'label' => __('A customer adds N products to the cart and gets free gifts'),
                'nameTemplate' => sprintf(
                    'Buy %1$s product%2$s, get %3$s product%4$s Free',
                    '<%= data.rule_settings.discount_step %>',
                    '<%= data.rule_settings.discount_step > 1 ? \'s\' : \'\'  %>',
                    '<%= data.rule_settings.discount_amount %>',
                    '<%= data.rule_settings.discount_amount > 1 ? \'s\' : \'\'  %>'
                ),
                'value' => self::SCENARIO_BUY_X_GET_Y
            ],
            [
                'label' => __('A customer reaches $X amount and gets free gifts'),
                'nameTemplate' => sprintf(
                    'Spent %1$s amount, get %2$s product%3$s Free',
                    '<%= data.rule_settings.discount_step %>',
                    '<%= data.rule_settings.discount_amount %>',
                    '<%= data.rule_settings.discount_amount > 1 ? \'s\' : \'\'  %>'
                ),
                'value' => self::SCENARIO_SPENT_X_GET_Y
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Scenario');
    }
}
