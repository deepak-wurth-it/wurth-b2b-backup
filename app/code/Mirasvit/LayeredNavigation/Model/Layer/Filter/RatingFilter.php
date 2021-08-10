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
use Mirasvit\LayeredNavigation\Model\Config\ExtraFilterConfigProvider;

class RatingFilter extends AbstractFilter
{
    private $attributeCode = ExtraFilterConfigProvider::RATING_FILTER;

    private $ratingValues  = [5, 4, 3, 2, 1];

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->_requestVar               = ExtraFilterConfigProvider::RATING_FILTER_FRONT_PARAM;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
    }

    public function apply(RequestInterface $request): self
    {
        if (!$this->extraFilterConfigProvider->isRatingFilterEnabled()) {
            return $this;
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = array_map(function ($v) {
            return (int)$v;
        }, explode(',', $attributeValue));

        $minValue = max($this->ratingValues);
        foreach ($attributeValue as $value) {
            $minValue = min($minValue, $value);
        }

        $this->getProductCollection()->addFieldToFilter(
            ExtraFilterConfigProvider::RATING_FILTER,
            $minValue
        );

        // add state
        if ($this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $labels = array_map(function ($value) {
                return $this->getOptionLabel($value);
            }, $attributeValue);

            $optionLabel = implode(', ', $labels);
            $this->addStateItem($this->_createItem($optionLabel, $attributeValue));
        } else {
            foreach ($attributeValue as $value) {
                $this->addStateItem($this->_createItem($this->getOptionLabel($value), $value));
            }
        }

        return $this;
    }

    public function getName(): string
    {
        $stockName = $this->extraFilterConfigProvider->getRatingFilterLabel();
        $stockName = $stockName ? : ExtraFilterConfigProvider::RATING_FILTER_DEFAULT_LABEL;

        return $stockName;
    }

    protected function _getItemsData(): array
    {
        if (!$this->extraFilterConfigProvider->isRatingFilterEnabled()) {
            return [];
        }

        if (!$this->configProvider->isMultiselectEnabled()) {
            return [];
        }

        $optionsFacetedData = $this->getProductCollection()->getExtendedFacetedData(
            $this->attributeCode,
            $this->configProvider->isMultiselectEnabled()
        );

        $cumulativeCount = 0;
        foreach ($this->ratingValues as $ratingValue) {
            $count           = isset($optionsFacetedData[$ratingValue]) ? $optionsFacetedData[$ratingValue]['count'] : 0;
            $cumulativeCount += $count;

            if ($count === 0) {
                continue;
            }

            $this->itemDataBuilder->addItemData(
                $ratingValue,
                $ratingValue,
                $cumulativeCount
            );
        }

        return $this->itemDataBuilder->build();
    }

    private function getOptionLabel(int $option): string
    {
        return (string)__('%1 & Up', $option);
    }
}
