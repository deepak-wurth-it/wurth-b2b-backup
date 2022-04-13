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



namespace Mirasvit\SearchMysql\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;

class Engine implements EngineInterface
{
    /**
     * @deprecated
     * @see EngineInterface::FIELD_PREFIX
     */
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     * @deprecated
     * @see EngineInterface::SCOPE_IDENTIFIER
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Catalog product visibility
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Is attribute filterable as term cache
     * @var array
     */
    private $termFilterableAttributeAttributeCache = [];

    /**
     * Engine constructor.
     *
     * @param \Magento\Catalog\Model\Product\Visibility                   $catalogProductVisibility
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->indexScopeResolver       = $indexScopeResolver;
    }

    /**
     * Retrieve allowed visibility values for current engine
     * @return int[]
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function processAttributeValue($attribute, $value)
    {
        if ($attribute->getIsSearchable()
            && in_array($attribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            return $value;
        } elseif ($this->isTermFilterableAttribute($attribute)
            || in_array($attribute->getAttributeCode(), ['visibility', 'status'])
        ) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(',', (string)$value);
            }
            if (!is_array($value)) {
                $value = [$value];
            }
            $valueMapper = function ($value) use ($attribute) {
                return Engine::ATTRIBUTE_PREFIX . $attribute->getAttributeCode() . '_' . $value;
            };

            return implode(' ', array_map($valueMapper, $value));
        }
    }

    /**
     * Is Attribute Filterable as Term
     *
     * @param \Magento\Catalog\Model\Entity\Attribute|\Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return bool
     */
    private function isTermFilterableAttribute($attribute)
    {
        $attributeId = $attribute->getAttributeId();
        if (!isset($this->termFilterableAttributeAttributeCache[$attributeId])) {
            $this->termFilterableAttributeAttributeCache[$attributeId]
                = in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                && ($attribute->getIsVisibleInAdvancedSearch()
                    || $attribute->getIsFilterable()
                    || $attribute->getIsFilterableInSearch());
        }

        return $this->termFilterableAttributeAttributeCache[$attributeId];
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array  $index
     * @param string $separator
     *
     * @return array
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        $indexData = [];
        foreach ($index as $attributeId => $value) {
            $indexData[$attributeId] = is_array($value) ? implode($separator, $value) : $value;
        }

        return $indexData;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }
}
