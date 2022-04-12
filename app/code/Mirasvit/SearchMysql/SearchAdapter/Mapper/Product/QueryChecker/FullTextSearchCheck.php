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



namespace Mirasvit\SearchMysql\SearchAdapter\Mapper\Product\QueryChecker;


use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;


class FullTextSearchCheck
{
    /**
     * Checks if $query requires full text search
     *
     * This is required to determine whether we need
     * to join catalog_eav_attribute table to search query or not
     *
     * In case when the $query does not requires full text search
     * - we can skip joining catalog_eav_attribute table because it becomes excessive
     *
     */
    public function isRequiredForQuery(QueryInterface $query): bool
    {
        return $this->processQuery($query);
    }

    private function processQuery(QueryInterface $query): bool
    {
        switch ($query->getType()) {
            case QueryInterface::TYPE_MATCH:
                return true;
                break;

            case QueryInterface::TYPE_BOOL:
                return $this->processBoolQuery($query);
                break;

            case QueryInterface::TYPE_FILTER:
                return $this->processFilterQuery($query);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $query->getType()));
        }
    }

    private function processBoolQuery(BoolExpression $query): bool
    {
        foreach ($query->getShould() as $shouldQuery) {
            if ($this->processQuery($shouldQuery)) {
                return true;
            }
        }

        foreach ($query->getMust() as $mustQuery) {
            if ($this->processQuery($mustQuery)) {
                return true;
            }
        }

        foreach ($query->getMustNot() as $mustNotQuery) {
            if ($this->processQuery($mustNotQuery)) {
                return true;
            }
        }

        return false;
    }

    private function processFilterQuery(Filter $query): bool
    {
        switch ($query->getReferenceType()) {
            case Filter::REFERENCE_QUERY:
                return $this->processQuery($query->getReference());
                break;

            case Filter::REFERENCE_FILTER:
                return false;
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown reference type \'%s\'',
                        $query->getReferenceType()
                    )
                );
        }
    }
}
