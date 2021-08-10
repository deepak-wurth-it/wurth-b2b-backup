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

namespace Mirasvit\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Model\Layer\FilterList;

class FilterExpander extends Template
{
    protected $_template = 'Mirasvit_LayeredNavigation::navigation/filterExpander.phtml';

    private   $config;

    private   $filterList;

    private   $layerResolver;

    public function __construct(
        ConfigProvider $config,
        FilterList $filterList,
        LayerResolver $layerResolver,
        Template\Context $context,
        array $data = []
    ) {
        $this->config        = $config;
        $this->filterList    = $filterList;
        $this->layerResolver = $layerResolver;

        parent::__construct($context, $data);
    }

    public function getOpenedFilterIndexes()
    {
        $indexes = [];

        $layer = $this->layerResolver->get();

        $filters = $this->filterList->getFilters($layer);

        $idx = 0;
        foreach ($filters as $filter) {
            if (!$filter->getItemsCount()) {
                continue;
            }

            if ($this->isActiveFilter($filter) || $this->config->isOpenFilter()) {
                $indexes[] = $idx;
            }

            $idx++;
        }

        return $indexes;
    }

    private function isActiveFilter(AbstractFilter $filter)
    {
        $activeFilters = $this->layerResolver->get()->getState()->getFilters();

        foreach ($activeFilters as $item) {
            if ($item->getFilter()->getRequestVar() === $filter->getRequestVar()) {
                return true;
            }
        }

        return false;
    }
}
