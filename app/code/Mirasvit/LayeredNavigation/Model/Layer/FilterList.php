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

namespace Mirasvit\LayeredNavigation\Model\Layer;

use Magento\Catalog\Model\Config\LayerCategoryConfig;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\HorizontalBarConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;

/**
 * @SuppressWarnings(PHPMD)
 */
class FilterList extends Layer\FilterList
{
    protected $filterTypes
        = [
            self::CATEGORY_FILTER  => Filter\CategoryFilter::class,
            self::ATTRIBUTE_FILTER => Filter\AttributeFilter::class,
            self::PRICE_FILTER     => Filter\DecimalFilter::class,
            self::DECIMAL_FILTER   => Filter\DecimalFilter::class,
        ];

    private   $isHorizontal;

    private   $additionalFilters;

    private   $attributeConfigRepository;

    private   $extraFilterConfigProvider;

    private   $horizontalBarConfigProvider;

    private   $configProvider;

    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeListInterface $filterableAttributes,
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        HorizontalBarConfigProvider $horizontalBarConfigProvider,
        AttributeConfigRepository $attributeConfigRepository,
        ConfigProvider $configProvider,
        LayerCategoryConfig $layerCategoryConfig,
        bool $isHorizontal = false,
        array $filters = [],
        array $additionalFilters = []
    ) {
        $this->isHorizontal                = $isHorizontal;
        $this->extraFilterConfigProvider   = $extraFilterConfigProvider;
        $this->horizontalBarConfigProvider = $horizontalBarConfigProvider;
        $this->attributeConfigRepository   = $attributeConfigRepository;
        $this->configProvider              = $configProvider;
        $this->additionalFilters           = $additionalFilters;

        parent::__construct($objectManager, $filterableAttributes, $layerCategoryConfig, $filters);
    }

    /**
     * Retrieve list of filters
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param Layer $layer
     *
     * @return array|AbstractFilter[]
     */
    public function getFilters(Layer $layer): array
    {
        if (!count($this->filters)) {
            $this->filters = [];

            if ($this->configProvider->isCategoryFilterVisibleInLayerNavigation()) {
                $this->filters[] = $this->objectManager->create($this->filterTypes[self::CATEGORY_FILTER], ['layer' => $layer]);
            }

            foreach ($this->filterableAttributes->getList() as $attribute) {
                $this->filters[] = $this->createAttributeFilter($attribute, $layer);
            }

            foreach ($this->filters as $key => $filter) {
                $attribute = $filter->getData('attribute_model');

                if (!$attribute) {
                    continue;
                }

                $attributeConfig = $this->attributeConfigRepository->getByAttributeCode($attribute->getAttributeCode());

                if (!$attributeConfig) {
                    continue;
                }

                if ($attributeConfig->getCategoryVisibilityMode() == AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_SHOW_IN_SELECTED
                    && !in_array($layer->getCurrentCategory()->getId(), $attributeConfig->getCategoryVisibilityIds())) {
                    unset($this->filters[$key]);
                }

                if ($attributeConfig->getCategoryVisibilityMode() == AttributeConfigInterface::CATEGORY_VISIBILITY_MODE_HIDE_IN_SELECTED
                    && in_array($layer->getCurrentCategory()->getId(), $attributeConfig->getCategoryVisibilityIds())) {
                    unset($this->filters[$key]);
                }
            }

            $this->applyFilterPosition($this->getAdditionalFilters($layer));

            $this->isHorizontal
                ? $this->makeHorizontalFilters()
                : $this->makeVerticalFilters();
        }

        return $this->filters;
    }

    private function applyFilterPosition(array $additionalFilters): void
    {
        foreach ($additionalFilters as $data) {
            foreach ($data as $position => $additionalFilter) {
                if (isset($this->filters[$position]) && $position != 0) {
                    $firstFilterPart  = array_slice($this->filters, 0, $position);
                    $secondFilterPart = array_slice($this->filters, $position);
                    $this->filters    = array_merge($firstFilterPart, [$additionalFilter], $secondFilterPart);
                } elseif ($position == 0) {
                    array_unshift($this->filters, $additionalFilter);
                } else {
                    $this->filters = array_merge($this->filters, [$additionalFilter]);
                }
            }
        }
    }

    /** @return AbstractFilter[] */
    private function getAdditionalFilters(Layer $layer): array
    {
        $additionalFilters = [];

        foreach ($this->additionalFilters as $filter => $class) {
            if ($this->extraFilterConfigProvider->isFilterEnabled($filter)) {
                $position = $this->extraFilterConfigProvider->getFilterPosition($filter);

                $additionalFilters[] = [
                    $position => $this->objectManager->create($class, ['layer' => $layer]),
                ];
            }
        }

        return $additionalFilters;
    }

    private function makeHorizontalFilters(): void
    {
        foreach ($this->filters as $key => $filter) {
            if ($this->horizontalBarConfigProvider->isDisplayInHorizontalBar($filter->getRequestVar())) {
                continue;
            }

            unset($this->filters[$key]);
        }
    }

    private function makeVerticalFilters(): void
    {
        foreach ($this->filters as $key => $filter) {
            if ($this->horizontalBarConfigProvider->isDisplayInSideBar($filter->getRequestVar())) {
                continue;
            }

            unset($this->filters[$key]);
        }
    }
}
