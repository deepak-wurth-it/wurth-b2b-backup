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
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;
use Mirasvit\LayeredNavigation\Service\SliderService;

/**
 * @SuppressWarnings(PHPMD)
 */
class DecimalFilter extends AbstractFilter
{

    /** Price delta for filter  */
    const PRICE_DELTA = 0.001;

    /**
     * @var array
     */
    protected static $isStateAdded = [];

    /**
     * @var bool
     */
    protected static $isAdded;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    private $priceCurrency;

    /**
     * @var array
     */
    private $facetedData;

    private $sliderService;

    private $attributeConfigRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        PriceFactory $dataProviderFactory,
        SliderService $sliderService,
        AttributeConfigRepository $attributeConfigRepository,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->priceCurrency             = $priceCurrency;
        $this->dataProvider              = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->sliderService             = $sliderService;
        $this->attributeConfigRepository = $attributeConfigRepository;
    }

    public function apply(RequestInterface $request): self
    {
        $attributeCode  = $this->getRequestVar();
        $attributeValue = $request->getParam($this->getRequestVar());

        if (!$attributeValue || !is_string($attributeValue)) {
            return $this;
        }

        $fromArray    = [];
        $toArray      = [];
        $filterParams = explode(',', $attributeValue);

        $productCollection = $this->getProductCollection();

        $labels = [];
        foreach ($filterParams as $filterParam) {
            $filterParamArray = preg_split('/[\-:]/', $filterParam);

            $from = isset($filterParamArray[0]) ? (float)$filterParamArray[0] : null;
            $to   = isset($filterParamArray[1]) ? (float)$filterParamArray[1] : null;

            $fromArray[] = $from ? $from : 0;
            $toArray[]   = $to ? $to : 10000000;

            $label    = $this->renderRangeLabel($from, $to);
            $labels[] = $label;

            if (!$this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
                $this->addState($label, $attributeValue);
            }
        }

        if ($this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $labels = count($labels) > 1 ? $labels : $labels[0];
            $this->addState($labels, $attributeValue);
        }

        $from = min($fromArray);
        $to   = max($toArray);

        $this->setFromToData(['from' => $from, 'to' => $to]);

        $productCollection->addFieldToFilter($attributeCode, ['from' => $from, 'to' => $to]);

        return $this;
    }

    /**
     * @return array
     */
    public function getFacetedData()
    {
        if ($this->facetedData === null) {
            /** @var \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();
            $attribute         = $this->getAttributeModel();

            $facets = $productCollection->getExtendedFacetedData($attribute->getAttributeCode(), true);

            $this->facetedData = $facets;
        }

        return $this->facetedData;
    }

    //    /**
    //     * Apply price range filter
    //     *
    //     * @param \Magento\Framework\App\RequestInterface $request
    //     *
    //     * @return $this
    //     * @SuppressWarnings(PHPMD.NPathComplexity)
    //     */
    //    public function getDefaultApply(\Magento\Framework\App\RequestInterface $request)
    //    {
    //        /**
    //         * Filter must be string: $fromPrice-$toPrice
    //         */
    //        $filter = $request->getParam($this->getRequestVar());
    //        if (!$filter || is_array($filter)) {
    //            return $this;
    //        }
    //
    //        $filterParams = explode(',', $filter);
    //
    //        // replace : with - to pass validateFilter
    //        $filteToCheck = str_replace(':', '-', $filterParams[0]);
    //        $filter       = $this->dataProvider->validateFilter($filteToCheck);
    //
    //        if (!$filter) {
    //            return $this;
    //        }
    //
    //        $this->dataProvider->setInterval($filter);
    //        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
    //        if ($priorFilters) {
    //            $this->dataProvider->setPriorIntervals($priorFilters);
    //        }
    //
    //        [$from, $to] = $filter;
    //        if ($to !== '' && !is_numeric($to)) {
    //            $to = '';
    //        }
    //
    //        self::$isAdded = true;
    //        $this->getLayer()->getProductCollection()->addFieldToFilter(
    //            'price',
    //            ['from' => $from, 'to' => empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA]
    //        );
    //        $this->setFromToData(['from' => $from, 'to' => $to]);
    //        $this->addState($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter);
    //
    //        return $this;
    //    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function getSliderData($url)
    {
        return $this->sliderService->getSliderData(
            $this->getFacetedData(),
            $this->getRequestVar(),
            (array)$this->getFromToData(),
            $url
        );
    }

    public function getCurrencyRate(): float
    {
        $rate = $this->_getData('currency_rate');

        if ($rate === null) {
            $rate = $this->_storeManager->getStore($this->getStoreId())
                ->getCurrentCurrencyRate();
        }

        if (!$rate) {
            $rate = 1;
        }

        return (float)$rate;
    }
    //
    //    /**
    //     * @param array  $facets
    //     * @param string $requestVar
    //     *
    //     * @return array
    //     */
    //    protected function getMinMaxData($facets, $requestVar)
    //    {
    //        $minMaxData    = [];
    //        $sliderDataKey = $this->sliderService->getSliderDataKey($requestVar);
    //        if (isset($facets[$sliderDataKey]['min'])
    //            && isset($facets[$sliderDataKey]['max'])) {
    //            $minMaxData['from'] = $facets[$sliderDataKey]['min'];
    //            $minMaxData['to']   = $facets[$sliderDataKey]['max'];
    //        }
    //
    //        return $minMaxData;
    //    }

    /**
     * Add data to state
     *
     * @param string|array $label
     * @param string|array $attributeValue
     *
     * @return bool
     */
    protected function addState($label, $attributeValue)
    {
        $state = is_array($attributeValue)
            ? $this->_requestVar . implode('_', $attributeValue)
            : $this->_requestVar . $attributeValue;

        if (isset(self::$isStateAdded[$state])) { //avoid double state adding (horizontal filters)
            return true;
        }

        if (is_array($attributeValue) && !$this->configProvider->isMultiselectEnabled()) {
            $attributeValue = implode('-', $attributeValue);
        }

        if (!is_array($attributeValue)) {
            $attributeValue = $this->getPreparedValue($this->_requestVar, $attributeValue);
        }

        if (!is_array($attributeValue) && strpos($attributeValue, ',') !== false) {
            $attributeValue = explode(',', $attributeValue);
        }

        if (is_array($attributeValue) && is_array($label)) {
            $this->getLayer()->getState()
                ->addFilter($this->_createItem(implode(', ', $label), implode(',', $attributeValue)));
        } elseif (is_array($attributeValue)) {
            foreach ($attributeValue as $attribute) {
                if (strpos($attribute, '-') !== false) {
                    $attributeArray = explode('-', $attribute);
                    $attributeLabel = $this->renderRangeLabel((float)$attributeArray[0], (float)$attributeArray[1]);
                    $this->addStateItem($this->_createItem($attributeLabel, $attribute));
                } else {
                    $this->addStateItem($this->_createItem($attribute, $attribute));
                }
            }
        } else {
            $this->addStateItem(
                $this->_createItem($label, $attributeValue)
            );
        }
        self::$isStateAdded[$state] = true;

        return true;
    }

    protected function _getItemsData(): array
    {
        //        $attribute         = $this->getAttributeModel();
        //        $this->_requestVar = $attribute->getAttributeCode();

        $facets = $this->getFacetedData();

        $data = [];

        if (count($facets) >= 1) {
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }

                $data[] = $this->prepareData($key, $count);
            }
        }

        return $data;
    }

    protected function prepareData(string $key, int $count): array
    {
        [$from, $to] = explode('_', $key);

        $from = $from == '*' ? $this->getFrom((float)$to) : (float)$from;
        $to   = $to == '*' ? null : (float)$to;

        $label = $this->renderRangeLabel(empty($from) ? 0 : $from, $to);

        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        return [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from'  => $from,
            'to'    => $to,
        ];
    }

    private function renderRangeLabel(?float $fromPrice, ?float $toPrice): ?string
    {
        if (strpos($fromPrice . $toPrice, ',') !== false) {
            return null;
        }

        $attributeConfig = $this->getAttributeConfig($this->_requestVar);
        $displayMode     = $attributeConfig->getDisplayMode();
        $valueTemplate   = $attributeConfig->getValueTemplate();

        if ($this->_requestVar === 'price') {
            $fromPrice = $fromPrice === null ? 0 : $fromPrice * $this->getCurrencyRate();
            $toPrice   = $toPrice === null ? '' : $toPrice * $this->getCurrencyRate();
        } else {
            $fromPrice = $fromPrice === null ? 0 : $fromPrice;
            $toPrice   = $toPrice === null ? '' : $toPrice;
        }

        if (!in_array($displayMode, [AttributeConfigInterface::DISPLAY_MODE_SLIDER, AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO])
            && $toPrice !== '') {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
        }

        if ($this->_requestVar === 'price') {
            $fromText = $this->priceCurrency->format($fromPrice);
            $toText   = $this->priceCurrency->format($toPrice);
        } else {
            $valueTemplate = $valueTemplate ? $valueTemplate : '{value}';

            $fromText = str_replace('{value}', round($fromPrice), $valueTemplate);
            $toText   = str_replace('{value}', round($toPrice), $valueTemplate);
        }

        if ($toPrice === '') {
            return (string)__('%1 and above', $fromText);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $fromText;
        } else {
            return (string)__('%1 - %2', $fromText, $toText);
        }
    }

    //    protected function getTo(float $from): float
    //    {
    //        $to       = '';
    //        $interval = $this->dataProvider->getInterval();
    //        if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
    //            $to = $interval[1];
    //        }
    //
    //        return $to;
    //    }

    private function getFrom(float $from): float
    {
        $to       = 0.0;
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = (float)$interval[0];
        }

        return $to;
    }

    private function getPreparedValue(string $requestVar, string $value): string
    {
        if ($requestVar != 'price' || $this->configProvider->isMultiselectEnabled()) {
            return $value;
        }

        return str_replace(',', '-', $value);
    }

    private function getAttributeConfig(string $attributeCode): AttributeConfigInterface
    {
        $attributeConfig = $this->attributeConfigRepository->getByAttributeCode($attributeCode);

        return $attributeConfig ? $attributeConfig : $this->attributeConfigRepository->create();
    }
}
