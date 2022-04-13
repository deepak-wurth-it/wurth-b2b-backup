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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\BaseSelectStrategy\StrategyMapper;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper\DimensionsProcessor;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper\FilterMapper;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer\SelectContainerBuilder;

class ProductIndexBuilder extends IndexBuilder
{
    private $dimensionsProcessor;

    private $selectContainerBuilder;

    private $baseSelectStrategyMapper;

    private $filterMapper;

    public function __construct(
        DimensionsProcessor $dimensionsProcessor,
        SelectContainerBuilder $selectContainerBuilder,
        StrategyMapper $baseSelectStrategyMapper,
        FilterMapper $filterMapper
    ) {
        $this->dimensionsProcessor      = $dimensionsProcessor;
        $this->selectContainerBuilder   = $selectContainerBuilder;
        $this->baseSelectStrategyMapper = $baseSelectStrategyMapper;
        $this->filterMapper             = $filterMapper;
    }

    public function build(RequestInterface $request): Select
    {
        $selectContainer = $this->selectContainerBuilder->buildByRequest($request);

        $baseSelectStrategy = $this->baseSelectStrategyMapper->mapSelectContainerToStrategy($selectContainer);

        $selectContainer = $baseSelectStrategy->createBaseSelect($selectContainer);
        $selectContainer = $this->filterMapper->applyFilters($selectContainer);
        $selectContainer = $this->dimensionsProcessor->processDimensions($selectContainer);

        return $selectContainer->getSelect();
    }
}
