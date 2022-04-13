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

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter\AliasResolver;

class StaticAttributeStrategy
{
    private $resourceConnection;

    private $aliasResolver;

    private $eavConfig;

    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        AliasResolver $aliasResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig          = $eavConfig;
        $this->aliasResolver      = $aliasResolver;
    }

    public function apply(FilterInterface $filter, Select $select): bool
    {
        $attribute      = $this->getAttributeByCode($filter->getField());
        $alias          = $this->aliasResolver->getAlias($filter);
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [$alias => $attribute->getBackendTable()],
            sprintf('%s.entity_id = ', $mainTableAlias)
            . $this->resourceConnection->getConnection()->quoteIdentifier("$alias.entity_id"),
            []
        );

        return true;
    }

    private function getAttributeByCode(string $field): Attribute
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }

    private function extractTableAliasFromSelect(Select $select): ?string
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
