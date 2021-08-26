<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Attribute\Edit\Tab\Element;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Context;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mirasvit\LayeredNavigation\Model\AttributeConfig\OptionConfig;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;
use Mirasvit\SeoFilter\Repository\RewriteRepository;

/**
 * @SuppressWarnings(PHPMD)
 */
class OptionsConfig extends Widget
{
    private $attributeConfigRepository;

    private $rewriteRepository;

    private $attribute;

    private $formFactory;

    private $eavConfig;

    public function __construct(
        AttributeConfigRepository $attributeConfigRepository,
        RewriteRepository $rewriteRepository,
        FormFactory $formFactory,
        Context $context,
        Config $eavConfig,
        Registry $registry
    ) {
        $this->attributeConfigRepository = $attributeConfigRepository;
        $this->rewriteRepository         = $rewriteRepository;
        $this->formFactory               = $formFactory;
        $this->eavConfig                 = $eavConfig;

        $this->attribute = $registry->registry('entity_attribute');

        parent::__construct($context);
    }

    public function getAttributeOptions(): array
    {
        $attribute = $this->getAttribute();

        $options = [];

        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if (isset($option['value']) && $option['value']) {
                $options[$option['value']] = [
                    'value' => $option['value'],
                    'name'  => $option['label'],
                ];
            }
        }

        $attrConfig = $this->attributeConfigRepository->getByAttributeCode($attribute->getAttributeCode());

        if (!$attrConfig) {
            $attrConfig = $this->attributeConfigRepository->create()
                ->setAttributeCode($attribute->getAttributeCode());

            $optionsConfig = [];
            foreach ($options as $option) {
                $optionConfig    = (new OptionConfig())
                    ->setOptionId((int)$option['value']);
                $optionsConfig[] = $optionConfig;
            }
            $attrConfig->setOptionsConfig($optionsConfig);
        }

        $optionsConfig = $attrConfig->getOptionsConfig();

        foreach ($optionsConfig as $optionConfig) {
            $optionId = $optionConfig->getOptionId();

            if (!isset($options[$optionId])) {
                continue;
            }

            $options[$optionId][OptionConfig::LABEL]               = $optionConfig->getLabel();
            $options[$optionId][OptionConfig::IS_FULL_IMAGE_WIDTH] = $optionConfig->isFullImageWidth();

            if ($optionConfig->getImagePath()) {
                $options[$optionId]['navigation_file_save'] = [
                    'url'  => $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                        . 'tmp/catalog/product/' . $optionConfig->getImagePath(),
                    'file' => $optionConfig->getImagePath(),
                ];
            }
        }

        return $options;
    }

    public function getImageField(string $fieldId = 'img_field', string $fieldName = 'img_field'): string
    {
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('label');

        $general = $form->addFieldset('fieldset_' . $fieldId, [
            'legend'  => __('Image'),
            'html_id' => 'fieldsethtml_' . $fieldId,
        ]);
        $general->addType('image1', ImageElement::class);
        $general->addField($fieldId, 'image1', [
            'label'    => __('Title'),
            'required' => true,
            'name'     => $fieldName,
            'value'    => '',
            'html_id'  => $fieldId,
        ]);

        return (string)$general->getChildrenHtml();
    }

    protected function _construct(): void
    {
        parent::_construct();

        $this->setTemplate('Mirasvit_LayeredNavigation::product/attribute/tab/element/options_config.phtml');
    }

    private function getAttribute(): AbstractAttribute
    {
        return $this->eavConfig->getAttribute('catalog_product', $this->attribute->getAttributeCode());
    }
}
