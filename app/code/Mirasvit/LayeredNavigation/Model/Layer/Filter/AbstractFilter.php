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
use Magento\Catalog\Model\Layer\Filter\Item;
use Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;

abstract class AbstractFilter extends Layer\Filter\AbstractFilter
{
    protected $configProvider;

    protected $stateBarConfigProvider;

    public function __construct(
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        $this->configProvider         = $context->configProvider;
        $this->stateBarConfigProvider = $context->stateBarConfigProvider;

        parent::__construct(
            $context->filterItemFactory,
            $context->storeManager,
            $layer,
            $context->itemDataBuilder,
            $data
        );
    }

    protected function getProductCollection(): Collection
    {
        /** @var Collection $collection */
        $collection = $this->getLayer()->getProductCollection();

        return $collection;
    }

    protected function addStateItem(Item $item): void
    {
        foreach ($this->getLayer()->getState()->getFilters() as $filter) {
            if ($filter->getValueString() === $item->getValueString()
                && $filter->getName() === $item->getName()) {
                return;
            }
        }

        $this->getLayer()->getState()->addFilter($item);
    }
}
