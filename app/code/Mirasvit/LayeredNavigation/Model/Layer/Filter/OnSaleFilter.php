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

class OnSaleFilter extends AbstractFilter
{
    private $attributeCode = ExtraFilterConfigProvider::ON_SALE_FILTER;

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->_requestVar               = ExtraFilterConfigProvider::ON_SALE_FILTER_FRONT_PARAM;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
    }

    public function apply(RequestInterface $request): self
    {
        if (!$this->extraFilterConfigProvider->isOnSaleFilterEnabled()) {
            return $this;
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (!$attributeValue) {
            return $this;
        }

        $this->getProductCollection()->addFieldToFilter($this->attributeCode, 1);

        $this->addState($this->getName(), $attributeValue);

        return $this;
    }

    public function getName(): string
    {
        $saleName = $this->extraFilterConfigProvider->getOnSaleFilterLabel();
        $saleName = ($saleName) ? : ExtraFilterConfigProvider::ON_SALE_FILTER_DEFAULT_LABEL;

        return $saleName;
    }

    protected function _getItemsData(): array
    {
        if (!$this->extraFilterConfigProvider->isOnSaleFilterEnabled()) {
            return [];
        }

        if (!$this->configProvider->isMultiselectEnabled()) {
            return [];
        }

        $optionsFacetedData = $this->getProductCollection()->getExtendedFacetedData($this->attributeCode);

        $count = isset($optionsFacetedData[1]) ? $optionsFacetedData[1]['count'] : 0;

        $optionsData = [
            [
                'label' => $this->getName(),
                'value' => 1,
                'count' => $count,
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

    private function addState(string $label, string $filter): void
    {
        $this->addStateItem($this->_createItem($label, $filter));
    }
}
