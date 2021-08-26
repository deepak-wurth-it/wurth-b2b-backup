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

namespace Mirasvit\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Framework\App\RequestInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;

class AttributeFilter extends AbstractFilter
{
    private $attributeConfigRepository;

    public function __construct(
        AttributeConfigRepository $attributeConfigRepository,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->attributeConfigRepository = $attributeConfigRepository;
    }

    public function apply(RequestInterface $request): self
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = explode(',', $attributeValue);

        $attribute = $this->getAttributeModel();

        // apply
        $this->getLayer()->getProductCollection()
            ->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);

        // add state
        if ($this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $labels = array_map(function ($value) {
                return $this->getOptionText($value);
            }, $attributeValue);

            $optionText = implode(', ', $labels);
            $this->addStateItem(
                $this->_createItem($optionText, $attributeValue)
            );
        } else {
            foreach ($attributeValue as $value) {
                $this->addStateItem(
                    $this->_createItem($this->getOptionText($value), $value)
                );
            }
        }

        $this->_items = null;

        return $this;

    }

    protected function _getItemsData(): array
    {
        $attribute = $this->getAttributeModel();

        /** @var \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();

        $optionsFacetedData = $collection->getExtendedFacetedData(
            $attribute->getAttributeCode(),
            $this->configProvider->isMultiselectEnabled()
        );


        $isAttributeFilterable = $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $collection->getSize();

        $options = $attribute->getFrontend()->getSelectOptions();

        $attrConfig = $this->getAttributeConfig();

        if ($attrConfig->getOptionsSortBy() === AttributeConfigInterface::OPTION_SORT_BY_LABEL) {
            usort($options, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }

        foreach ($options as $option) {
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize);
        }

        return $this->itemDataBuilder->build();
    }

    private function buildOptionData(array $option, bool $isAttributeFilterable, array $optionsFacetedData, int $productSize): void
    {
        $value = (string)$this->getOptionValue($option);

        if (empty($value)) {
            return;
        }

        $count = $this->getOptionCount($value, $optionsFacetedData);

        if ($isAttributeFilterable && $count === 0) {
            return;
        }

        $this->itemDataBuilder->addItemData(
            strip_tags((string)$option['label']),
            $value,
            $count
        );
    }

    private function getOptionValue(array $option): ?string
    {
        if (empty($option['value']) || !is_numeric($option['value'])) {
            return null;
        }

        return (string)$option['value'];
    }

    private function getOptionCount(string $value, array $optionsFacetedData): int
    {
        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }

    private function getAttributeConfig(): AttributeConfigInterface
    {
        $attrConfig = $this->attributeConfigRepository->getByAttributeCode(
            $this->getAttributeModel()->getAttributeCode()
        );

        return $attrConfig ? $attrConfig : $this->attributeConfigRepository->create();
    }
}
