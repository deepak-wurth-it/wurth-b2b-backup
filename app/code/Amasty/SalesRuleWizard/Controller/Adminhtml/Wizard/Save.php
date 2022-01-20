<?php

namespace Amasty\SalesRuleWizard\Controller\Adminhtml\Wizard;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Backend\Model\Session $session
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->session = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var $model \Magento\SalesRule\Model\Rule */
        $data = $this->getRequest()->getParams();
        $scenario = $this->getRequest()->getParam('scenario');

        if (!isset($data['apply_settings'], $data['rule_settings'], $data['additional']) || !$scenario) {
            $this->messageManager->addErrorMessage(__('Form Data is empty'));
            $this->_redirect('*/*/index');
            return;
        }

        $model = $this->ruleFactory->create();

        $ruleData = array_merge($data['rule_settings'], $data['apply_settings'], $data['additional']);

        if ($ruleData['is_conditions'] && isset($data['apply_settings']['rule']['conditions'])) {
            $ruleData['conditions'] = $data['apply_settings']['rule']['conditions'];
        }
        if ($ruleData['is_actions'] && isset($data['rule_settings']['rule']['actions'])) {
            $ruleData['actions'] = $data['rule_settings']['rule']['actions'];
        }
        unset($ruleData['rule']);
        $ruleData['name'] = $data['additional']['rule_name'];
        $ruleData['stop_rules_processing'] = 0;

        $ruleData['coupon_type'] = \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON;
        if ($data['apply_settings']['is_coupon']) {
            $ruleData['coupon_type'] = \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC;
        }

        switch ($data['apply_settings']['apply_time']) {
            case \Amasty\SalesRuleWizard\Model\OptionsProvider\ApplyTime::FIRST_TIME:
                $data['apply_settings']['maximum_times'] = 1;
                break;
            case \Amasty\SalesRuleWizard\Model\OptionsProvider\ApplyTime::EVERY_TIME:
                $data['apply_settings']['maximum_times'] = 0;
                break;
            default:
                $data['apply_settings']['maximum_times'] = (int)$data['apply_settings']['maximum_times'];
        }
        $ruleData['discount_qty'] =
            $data['rule_settings']['discount_amount'] * $data['apply_settings']['maximum_times'];

        $ruleData['discount_step'] = (int)$data['rule_settings']['discount_step'];
        if (!$ruleData['discount_step']) {
            $ruleData['discount_step'] = 1;
        }

        $ruleData['extension_attributes']['ampromo_rule']['sku'] = $this->convertGridToRow($data);

        switch ($scenario) {
            case \Amasty\SalesRuleWizard\Block\Adminhtml\Steps\Initial::SCENARIO_BUY_X_GET_Y:
                $ruleData['simple_action'] = \Amasty\Promo\Api\Data\GiftRuleInterface::PER_PRODUCT;
                if ($data['rule_settings']['is_same_product']) {
                    $ruleData['simple_action'] = \Amasty\Promo\Api\Data\GiftRuleInterface::SAME_PRODUCT;
                }
                break;
            case \Amasty\SalesRuleWizard\Block\Adminhtml\Steps\Initial::SCENARIO_SPENT_X_GET_Y:
                $ruleData['simple_action'] = \Amasty\Promo\Api\Data\GiftRuleInterface::SPENT;
                break;
        }

        $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
        if ($validateResult !== true) {
            foreach ($validateResult as $errorMessage) {
                $this->messageManager->addErrorMessage($errorMessage);
            }
            $this->session->setPageData($ruleData);
            $this->_redirect('*/*', ['id' => $model->getId()]);
            return;
        }

        try {
            $model->loadPost($ruleData);
            $model->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->session->setPageData($ruleData);
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rule_id');
            if (!empty($id)) {
                $this->_redirect('*/*', ['id' => $id]);
            } else {
                $this->_redirect('*/*');
            }
            return;
        } catch (\Exception $e) {
            $this->session->setPageData($ruleData);
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('*/*', ['id' => $this->getRequest()->getParam('rule_id')]);
            return;
        }

        $this->addSuccessMessage($model);

        $this->_redirect('sales_rule/promo_quote/index');
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function convertGridToRow($data)
    {
        if (!isset($data['rule_settings']['free_gifts']['products'])) {
            return '';
        }

        $grid = $data['rule_settings']['free_gifts']['products'];

        usort($grid, function ($a, $b) {
            return $a['position'] - $b['position'];
        });

        $skus = [];
        foreach ($grid as $product) {
            $skus[] = $product['sku'];
        }

        return implode(', ', $skus);
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $model
     */
    protected function addSuccessMessage($model)
    {
        $editUrl = $this->getUrl('sales_rule/promo_quote/edit', ['id' => $model->getId()]);

        $message = __('The Rule was saved successfully!');

        $isFreeGift = strpos($model->getSimpleAction(), 'ampromo_') !== false;

        if ($isFreeGift) {
            $message .= '<br/>' . __(
                'You can find more options related to taxes and shipping rates for free items <a href="%1">here</a>.',
                $editUrl
            );
        }
        if ($model->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $message .= '<br/>' .
                __('If you want to use multiple coupon codes with auto '.
                    'generation - you can find these settings in the section "Manage coupon codes".');
        }
        if ($isFreeGift && $model->getSimpleAction() != \Amasty\Promo\Api\Data\GiftRuleInterface::SPENT) {
            $message .= '<br/>' .
                __('Don\'t forget to configure promo banners and product '.
                    'labels to highlight your promotion on the storefront.');
        }

        $this->messageManager->addComplexNoticeMessage(
            'amastyRuleWizardInfo',
            ['text' => $message]
        );
    }
}
