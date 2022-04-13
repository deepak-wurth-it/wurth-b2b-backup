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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\Filter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Search\Request\FilterInterface;

class CustomAttributeFilterCheck
{
    private $eavConfig;

    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    public function isCustom(FilterInterface $filter): bool
    {
        $attribute = $this->getAttributeByCode($filter->getField());

        return $attribute
            && $filter->getType() === FilterInterface::TYPE_TERM
            && in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'], true);
    }

    private function getAttributeByCode(string $field): Attribute
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $field);
    }
}
