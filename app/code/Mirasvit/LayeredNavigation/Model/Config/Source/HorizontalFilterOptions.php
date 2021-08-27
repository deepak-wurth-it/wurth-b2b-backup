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

namespace Mirasvit\LayeredNavigation\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;

class HorizontalFilterOptions implements OptionSourceInterface
{
    const ALL_FILTERABLE_ATTRIBUTES = '*';

    private $attributeCollection;

    public function __construct(
        AttributeCollection $attributeCollection
    ) {
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray(): array
    {
        $options = [
            ['value' => HorizontalFilterOptions::ALL_FILTERABLE_ATTRIBUTES,
             'label' => __('All Filterable Attributes'),
            ],
        ];

        return array_merge($options, $this->getFilteredAttributesOptions());
    }

    private function getFilteredAttributesOptions(): array
    {
        $filteredAttributesOptions = [];

        $collection = clone $this->attributeCollection; //need use clear collection for next usage
        $collection
            ->join(
                ['cea' => $collection->getTable('catalog_eav_attribute')],
                'cea.attribute_id = main_table.attribute_id',
                []
            )->addFieldToFilter('cea.is_filterable', 1);

        foreach ($collection->getItems() as $item) {
            $filteredAttributesOptions[] = [
                'value' => $item->getAttributeCode(),
                'label' => $item->getFrontendLabel(),
            ];
        }

        $filteredAdditionalAttributesOptions = $this->getAdditionalFilters();

        $filteredAttributesOptions = array_merge(
            $filteredAttributesOptions,
            $filteredAdditionalAttributesOptions
        );

        return $filteredAttributesOptions;
    }

    private function getAdditionalFilters(): array
    {
        $filteredAdditionalAttributesOptions = [];

        $filteredAdditionalAttributesOptions[] = [
            'value' => ExtraFilterConfigProvider::NEW_FILTER_FRONT_PARAM,
            'label' => ExtraFilterConfigProvider::NEW_FILTER_DEFAULT_LABEL,
        ];

        $filteredAdditionalAttributesOptions[] = [
            'value' => ExtraFilterConfigProvider::ON_SALE_FILTER_FRONT_PARAM,
            'label' => ExtraFilterConfigProvider::ON_SALE_FILTER_DEFAULT_LABEL,
        ];

        $filteredAdditionalAttributesOptions[] = [
            'value' => ExtraFilterConfigProvider::STOCK_FILTER_FRONT_PARAM,
            'label' => ExtraFilterConfigProvider::STOCK_FILTER_DEFAULT_LABEL,
        ];

        $filteredAdditionalAttributesOptions[] = [
            'value' => ExtraFilterConfigProvider::RATING_FILTER_FRONT_PARAM,
            'label' => ExtraFilterConfigProvider::RATING_FILTER_DEFAULT_LABEL,
        ];

        return $filteredAdditionalAttributesOptions;
    }
}
