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

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Attribute\Edit\Tab\Fieldset;

use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory as DependencyFieldFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\Source\AttributeDisplayModeSource;

class DisplayFieldset extends Fieldset
{
    private $attribute;

    /**
     * @var AttributeConfigInterface
     */
    private $attributeConfig;

    private $attributeDisplayModeSource;

    private $layout;

    private $dependencyFieldFactory;

    public function __construct(
        AttributeDisplayModeSource $attributeDisplayModeSource,
        LayoutInterface $layout,
        DependencyFieldFactory $dependencyFieldFactory,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->attributeDisplayModeSource = $attributeDisplayModeSource;
        $this->attributeConfig            = $data[AttributeConfigInterface::class];
        $this->attribute                  = $data[Attribute::class];
        $this->layout                     = $layout;
        $this->dependencyFieldFactory     = $dependencyFieldFactory;

        parent::__construct($factoryElement, $factoryCollection, $escaper, [
            'legend' => __('Appearance'),
        ]);
    }

    /**
     * @return string
     */
    public function getBasicChildrenHtml()
    {
        $displayModeField = $this->addField(AttributeConfigInterface::DISPLAY_MODE, 'select', [
            'name'   => AttributeConfigInterface::DISPLAY_MODE,
            'label'  => __('Display Mode'),
            'values' => $this->attributeDisplayModeSource->toOptionArrayByType($this->attribute),
            'value'  => $this->attributeConfig->getDisplayMode(),
        ]);

        $valueTemplateField = $this->addField(AttributeConfigInterface::VALUE_TEMPLATE, 'text', [
            'name'  => AttributeConfigInterface::VALUE_TEMPLATE,
            'label' => __('Value Template'),
            'value' => $this->attributeConfig->getValueTemplate(),
            'note'  => 'Example: {value}cm, {value}". Leave empty to use default price format.',
        ]);

        $valueTemplateDependence = $this->dependence($displayModeField, $valueTemplateField, [
            AttributeConfigInterface::DISPLAY_MODE_SLIDER,
            AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
            AttributeConfigInterface::DISPLAY_MODE_FROM_TO,
        ]);

        $searchBoxFieldset = $this->addField(AttributeConfigInterface::IS_SHOW_SEARCH_BOX, 'select', [
            'name'   => AttributeConfigInterface::IS_SHOW_SEARCH_BOX,
            'label'  => __('Show Search Box'),
            'value'  => $this->attributeConfig->isShowSearchBox(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $searcBoxDependence = $this->dependence($displayModeField, $searchBoxFieldset, [
            AttributeConfigInterface::DISPLAY_MODE_LABEL,
        ]);

        return parent::getBasicChildrenHtml() . $valueTemplateDependence . $searcBoxDependence;
    }

    private function dependence(AbstractElement $parentElement, AbstractElement $affectedField, array $values)
    {
        /** @var Dependence $dependence */
        $dependence = $this->layout->createBlock(Dependence::class);

        $dependence->addFieldMap(
            $parentElement->getHtmlId(),
            $parentElement->getName()
        )->addFieldMap(
            $affectedField->getHtmlId(),
            $affectedField->getName()
        )->addFieldDependence(
            $affectedField->getName(),
            $parentElement->getName(),
            $this->dependencyFieldFactory->create([
                'fieldData'   => [
                    'value'     => implode(',', $values),
                    'separator' => ',',
                    'negative'  => false,
                ],
                'fieldPrefix' => '',
            ])
        );

        return $dependence->toHtml();
    }
}
