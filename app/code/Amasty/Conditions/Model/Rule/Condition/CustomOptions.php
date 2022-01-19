<?php

namespace Amasty\Conditions\Model\Rule\Condition;

/**
 * Product Custom options ID condition with chooser
 * @since 1.4.0
 */
class CustomOptions extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var string
     */
    protected $_inputType = 'multiselect';

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|null
     */
    private $valueElement;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(\Amasty\Conditions\Model\Rule\Condition\CustomOptions::class);
        $this->url = $url;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = ['multiselect' => ['==', '!=', '()', '!()']];
            $this->_arrayInputTypes = ['multiselect'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Render chooser trigger
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
            $this->_assetRepo->getUrl(
                'images/rule_chooser_trigger.gif'
            ) . '" alt="" class="v-middle rule-chooser-trigger" title="' . __(
                'Open Chooser'
            ) . '" /></a>';
    }

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Chooser URL getter
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        return $this->url->getUrl(
            \Amasty\Conditions\Controller\Adminhtml\ProductCustomOptions\ChooserGrid::URL_PATH,
            ['value_element_id' => $this->valueElement->getId(), 'form' => $this->getJsFormObject()]
        );
    }

    /**
     * Enable chooser selection button
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        return true;
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        $this->valueElement = $this->getValueElement();

        return $this->getTypeElementHtml() . __(
            'If Product Custom Options IDs %1 %2',
            $this->getOperatorElementHtml(),
            $this->valueElement->getHtml()
        ) .
            $this->getRemoveLinkHtml() .
            '<div class="rule-chooser" url="' .
            $this->getValueElementChooserUrl() .
            '"></div>';
    }

    /**
     * Specify allowed comparison operators
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption(
            [
                '==' => __('matches'),
                '!=' => __('does not match'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );

        return $this;
    }

    /**
     * Validate Cart Item
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $model
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attributeValue = [];

        if ($option = $model->getOptionByCode('option_ids')) {
            $attributeValue = array_map('trim', explode(',', $option->getValue()));
        }

        return $this->validateAttribute($attributeValue);
    }
}
