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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\Source\CategoryTreeSource;
use Mirasvit\LayeredNavigation\Model\Config\Source\DisplayInCategoriesOptions;
use Mirasvit\LayeredNavigation\Model\Config\Source\VisibleInCategorySource;

/**
 * @SuppressWarnings(PHPMD)
 */
class VisibilityFieldset extends Fieldset
{
    private $visibleInCategorySource;

    private $categoryTreeSource;

    private $layout;

    private $request;

    /**
     * @var Attribute
     */
    private $attribute;

    private $dependencyFieldFactory;

    /**
     * @var AttributeConfigInterface
     */
    private $attributeConfig;

    public function __construct(
        VisibleInCategorySource $visibleInCategorySource,
        CategoryTreeSource $categoryTreeSource,
        LayoutInterface $layout,
        DependencyFieldFactory $dependencyFieldFactory,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        RequestInterface $request,
        array $data = []
    ) {
        $this->visibleInCategorySource    = $visibleInCategorySource;
        $this->categoryTreeSource         = $categoryTreeSource;
        $this->layout                     = $layout;
        $this->dependencyFieldFactory     = $dependencyFieldFactory;

        $this->attributeConfig = $data[AttributeConfigInterface::class];
        $this->attribute       = $data[Attribute::class];
        $this->request         = $request;

        parent::__construct($factoryElement, $factoryCollection, $escaper, [
            'legend' => __('Visibility'),
        ]);
    }

    /**
     * @return string
     */
    public function getBasicChildrenHtml()
    {
        $visibilityField = $this->addField(
            AttributeConfigInterface::CATEGORY_VISIBILITY_MODE,
            'select',
            [
                'name'   => AttributeConfigInterface::CATEGORY_VISIBILITY_MODE,
                'label'  => __('Categories Visibility Mode'),
                'values' => $this->visibleInCategorySource->toOptionArray(),
                'value'  => $this->attributeConfig->getCategoryVisibilityMode(),
            ]
        );

        $categoryField = $this->addField(
            AttributeConfigInterface::CATEGORY_VISIBILITY_IDS,
            'multiselect',
            [
                'name'   => AttributeConfigInterface::CATEGORY_VISIBILITY_IDS,
                'label'  => __('Categories'),
                'style'  => 'height: 40rem;',
                'values' => $this->categoryTreeSource->toOptionArray(),
                'value'  => $this->attributeConfig->getCategoryVisibilityIds(),
            ]
        );

        /** @var Dependence $dependence */
        $dependence = $this->layout->createBlock(Dependence::class);

        $dependence->addFieldMap(
            $visibilityField->getHtmlId(),
            $visibilityField->getName()
        )->addFieldMap(
            $categoryField->getHtmlId(),
            $categoryField->getName()
        )->addFieldDependence(
            $categoryField->getName(),
            $visibilityField->getName(),
            $this->dependencyFieldFactory->create([
                'fieldData'   => [
                    'value'    => AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_ALL,
                    'negative' => true,
                ],
                'fieldPrefix' => '',
            ])
        );

        return parent::getBasicChildrenHtml() . $dependence->toHtml();
    }
}
