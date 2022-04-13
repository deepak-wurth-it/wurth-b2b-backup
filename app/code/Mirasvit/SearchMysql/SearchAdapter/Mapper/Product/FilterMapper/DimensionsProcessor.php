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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\FilterMapper;


use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\SelectContainer\SelectContainer;

class DimensionsProcessor
{
    private $conditionManager;

    private $dimensionScopeResolver;

    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $dimensionScopeResolver
    ) {
        $this->conditionManager       = $conditionManager;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
    }

    public function processDimensions(SelectContainer $selectContainer): SelectContainer
    {
        $query = $this->conditionManager->combineQueries(
            $this->prepareDimensions($selectContainer->getDimensions()),
            Select::SQL_OR
        );

        if (!empty($query)) {
            $select = $selectContainer->getSelect();
            $select->where($this->conditionManager->wrapBrackets($query));
            $selectContainer = $selectContainer->updateSelect($select);
        }

        return $selectContainer;
    }

    private function prepareDimensions(array $dimensions): array
    {
        $preparedDimensions = [];

        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
            );
        }

        return $preparedDimensions;
    }
}
