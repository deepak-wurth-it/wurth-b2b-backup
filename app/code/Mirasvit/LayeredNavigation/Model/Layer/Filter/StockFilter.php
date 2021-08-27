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

class StockFilter extends AbstractFilter
{
    private $attributeCode = ExtraFilterConfigProvider::STOCK_FILTER;

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->_requestVar               = ExtraFilterConfigProvider::STOCK_FILTER_FRONT_PARAM;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
    }

    public function apply(RequestInterface $request): self
    {
        if (!$this->extraFilterConfigProvider->isStockFilterEnabled()) {
            return $this;
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = array_map(function ($v) {
            return (int)$v;
        }, explode(',', $attributeValue));

        $this->getProductCollection()->addFieldToFilter(
            $this->attributeCode, $attributeValue
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
        $stockName = $this->extraFilterConfigProvider->getStockFilterLabel();
        $stockName = ($stockName) ? : ExtraFilterConfigProvider::STOCK_FILTER_DEFAULT_LABEL;

        return $stockName;
    }

    protected function _getItemsData(): array
    {
        if (!$this->extraFilterConfigProvider->isStockFilterEnabled()) {
            return [];
        }

        if (!$this->configProvider->isMultiselectEnabled()) {
            return [];
        }

        $optionsFacetedData = $this->getProductCollection()
            ->getExtendedFacetedData($this->attributeCode, $this->configProvider->isMultiselectEnabled());

        $inStockValue    = 2;
        $outOfStockValue = 1;

        $optionsData = [
            [
                'label' => $this->getOptionLabel(ExtraFilterConfigProvider::IN_STOCK_FILTER),
                'value' => ExtraFilterConfigProvider::IN_STOCK_FILTER,
                'count' => isset($optionsFacetedData[$inStockValue]) ? $optionsFacetedData[$inStockValue]['count'] : 0,
            ],
            [
                'label' => $this->getOptionLabel(ExtraFilterConfigProvider::OUT_OF_STOCK_FILTER),
                'value' => ExtraFilterConfigProvider::OUT_OF_STOCK_FILTER,
                'count' => isset($optionsFacetedData[$outOfStockValue]) ? $optionsFacetedData[$outOfStockValue]['count'] : 0,
            ],
        ];

        foreach ($optionsData as $data) {
            if ($data['count'] < 1) {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $data['label'],
                $data['value'],
                $data['count']
            );
        }

        return $this->itemDataBuilder->build();
    }

    private function getOptionLabel(int $option): string
    {
        $stateLabel = ($option == ExtraFilterConfigProvider::IN_STOCK_FILTER)
            ? $this->extraFilterConfigProvider->getInStockFilterLabel()
            : $this->extraFilterConfigProvider->getOutOfStockFilterLabel();

        if (!$stateLabel) {
            $stateLabel = ($option == ExtraFilterConfigProvider::IN_STOCK_FILTER)
                ? 'In Stock'
                : 'Out of Stock';
        }

        return $stateLabel;
    }
}
