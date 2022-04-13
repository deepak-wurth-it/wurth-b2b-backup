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
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Mirasvit\SearchMysql\SearchAdapter\Mapper\Filter\AliasResolver;

class FilterContext
{
    private $exclusionStrategy;

    private $eavConfig;

    private $staticAttributeStrategy;

    public function __construct(
        EavConfig $eavConfig,
        ExclusionStrategy $exclusionStrategy,
        StaticAttributeStrategy $staticAttributeStrategy
    ) {
        $this->eavConfig               = $eavConfig;
        $this->exclusionStrategy       = $exclusionStrategy;
        $this->staticAttributeStrategy = $staticAttributeStrategy;
    }

    public function apply(FilterInterface $filter, Select $select): bool
    {
        $isApplied = $this->exclusionStrategy->apply($filter, $select);

        if (!$isApplied) {
            $attribute = $this->getAttributeByCode($filter->getField());
            if ($attribute) {
                if ($filter->getType() === \Magento\Framework\Search\Request\FilterInterface::TYPE_TERM
                    && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                ) {
                    $isApplied = false;
                } elseif ($attribute->getBackendType() === AbstractAttribute::TYPE_STATIC) {
                    $isApplied = $this->staticAttributeStrategy->apply($filter, $select);
                }
            }
        }

        return $isApplied;
    }

    private function getAttributeByCode(string $field): Attribute
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }
}
