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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\CustomAttributeFilterCheck;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\FiltersExtractor;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper\VisibilityFilter;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\QueryChecker\FullTextSearchCheck;

class SelectContainerBuilder
{
    private $selectContainerFactory;

    private $fullTextSearchCheck;

    private $customAttributeFilterCheck;

    private $filtersExtractor;

    private $scopeConfig;

    private $resource;

    public function __construct(
        SelectContainerFactory $selectContainerFactory,
        FullTextSearchCheck $fullTextSearchCheck,
        CustomAttributeFilterCheck $customAttributeFilterCheck,
        FiltersExtractor $filtersExtractor,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource
    ) {
        $this->selectContainerFactory     = $selectContainerFactory;
        $this->fullTextSearchCheck        = $fullTextSearchCheck;
        $this->customAttributeFilterCheck = $customAttributeFilterCheck;
        $this->filtersExtractor           = $filtersExtractor;
        $this->scopeConfig                = $scopeConfig;
        $this->resource                   = $resource;
    }

    public function buildByRequest(RequestInterface $request): SelectContainer
    {
        $nonCustomAttributesFilters = [];
        $customAttributesFilters    = [];
        $visibilityFilter           = null;

        foreach ($this->filtersExtractor->extractFiltersFromQuery($request->getQuery()) as $filter) {
            if ($this->customAttributeFilterCheck->isCustom($filter)) {
                if ($filter->getField() === VisibilityFilter::VISIBILITY_FILTER_FIELD) {
                    $visibilityFilter = clone $filter;
                } else {
                    $customAttributesFilters[] = clone $filter;
                }
            } else {
                $nonCustomAttributesFilters[] = clone $filter;
            }
        }

        $data = [
            'select'                     => $this->resource->getConnection()->select(),
            'nonCustomAttributesFilters' => $nonCustomAttributesFilters,
            'customAttributesFilters'    => $customAttributesFilters,
            'dimensions'                 => $request->getDimensions(),
            'isFullTextSearchRequired'   => $this->fullTextSearchCheck->isRequiredForQuery($request->getQuery()),
            'isShowOutOfStockEnabled'    => $this->isSetShowOutOfStockFlag(),
            'usedIndex'                  => $request->getIndex(),
        ];

        if ($visibilityFilter !== null) {
            $data['visibilityFilter'] = $visibilityFilter;
        }

        return $this->selectContainerFactory->create($data);
    }

    private function isSetShowOutOfStockFlag(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
    }
}
