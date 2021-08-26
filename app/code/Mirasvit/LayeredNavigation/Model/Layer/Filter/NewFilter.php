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

class NewFilter extends AbstractFilter
{
    private $attributeCode = ExtraFilterConfigProvider::NEW_FILTER;

    private $extraFilterConfigProvider;

    public function __construct(
        ExtraFilterConfigProvider $extraFilterConfigProvider,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->_requestVar               = ExtraFilterConfigProvider::NEW_FILTER_FRONT_PARAM;
        $this->extraFilterConfigProvider = $extraFilterConfigProvider;
    }

    public function apply(RequestInterface $request): self
    {
        if (!$this->extraFilterConfigProvider->isNewFilterEnabled()) {
            return $this;
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (!$attributeValue) {
            return $this;
        }

        $this->getProductCollection()->addFieldToFilter($this->attributeCode, 1);

        $this->addStateItem(
            $this->_createItem((string)__('Yes'), $attributeValue)
        );

        return $this;
    }

    public function getName(): string
    {
        $newName = $this->extraFilterConfigProvider->getNewFilterLabel();
        $newName = $newName ? : ExtraFilterConfigProvider::NEW_FILTER_DEFAULT_LABEL;

        return $newName;
    }

    protected function _getItemsData(): array
    {
        if (!$this->extraFilterConfigProvider->isNewFilterEnabled()) {
            return [];
        }

        if (!$this->configProvider->isMultiselectEnabled()) {
            return [];
        }

        $optionsFacetedData = $this->getProductCollection()->getExtendedFacetedData(
            $this->attributeCode,
            $this->configProvider->isMultiselectEnabled()
        );

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
}
