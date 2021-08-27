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

namespace Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as GenericCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Search\Api\SearchInterface;
use Mirasvit\Core\Service\SerializeService;

class Collection extends GenericCollection
{
    private $facetCache = [];

    public function getExtendedFacetedData(string $field, bool $exclude = false, int $allowedValue = null): array
    {
        if ($exclude === false) {
            try {
                return parent::getFacetedData($field);
            } catch (\Exception$e) {
                return [];
            }
        }

        $criteriaBuilder = $this->createCriteriaBuilder($field, $allowedValue);
        $criteriaHash    = $this->getCriteriaBuilderHash($criteriaBuilder);

        if (!isset($this->facetCache[$criteriaHash])) {
            $aggregations = $this->getSearch()->search(
                $this->getSearchCriteriaResolver($criteriaBuilder)->resolve()
            )->getAggregations();

            $facetData = [];

            if ($aggregations !== null) {
                foreach ($aggregations->getBuckets() as $bucket) {
                    foreach ($bucket->getValues() as $value) {
                        $metrics = $value->getMetrics();

                        $facetData[$bucket->getName()][$metrics['value']] = $metrics;
                    }
                }
            }

            $this->facetCache[$criteriaHash] = $facetData;
        }

        $facetData = $this->facetCache[$criteriaHash];

        $bucketName = $field . RequestGenerator::BUCKET_SUFFIX;

        return isset($facetData[$bucketName])
            ? $facetData[$bucketName]
            : [];
    }

    private function createCriteriaBuilder(string $field, int $allowedValue = null): SearchCriteriaBuilder
    {
        $originalCriteriaBuilder = $this->getSearchCriteriaBuilder();

        $searchCriteria = $originalCriteriaBuilder->create();

        /** @var SearchCriteriaBuilder $newCriteriaBuilder */
        $newCriteriaBuilder = ObjectManager::getInstance()->create(SearchCriteriaBuilder::class);

        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                if (in_array($filter->getField(), [
                    $field,
                    $field . '.from',
                    $field . '.to',
                    $field . '_ids',
                ])) {
                    if ($allowedValue === null) {
                        continue;
                    }

                    if ($allowedValue !== (int)$filter->getValue()) {
                        continue;
                    }
                }

                $newCriteriaBuilder->addFilter($filter);
            }
        }

        $newCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(1);

        return $newCriteriaBuilder;
    }

    private function getCriteriaBuilderHash(SearchCriteriaBuilder $criteriaBuilder): string
    {
        $hash = '';
        foreach ($criteriaBuilder->create()->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $value = $filter->getValue();
                $value = is_array($value) ? SerializeService::encode($value) : $value;

                $hash .= $filter->getField() . $filter->getConditionType() . $value;
            }
        }

        return $hash;
    }

    private function getSearchCriteriaBuilder(): SearchCriteriaBuilder
    {
        $ref = new \ReflectionMethod(GenericCollection::class, 'getSearchCriteriaBuilder');
        $ref->setAccessible(true);

        return $ref->invoke($this);
    }

    private function getSearchCriteriaResolver(SearchCriteriaBuilder $criteriaBuilder): SearchCriteriaResolverInterface
    {
        return $this->getSearchCriteriaResolverFactory()->create(
            [
                'builder'           => $criteriaBuilder,
                'collection'        => $this,
                'searchRequestName' => $this->getSearchRequestName(),
                'currentPage'       => 1,
                'size'              => 1,
                'orders'            => [],
            ]
        );

    }

    private function getSearchRequestName(): string
    {
        $ref = new \ReflectionProperty(GenericCollection::class, 'searchRequestName');
        $ref->setAccessible(true);

        return $ref->getValue($this);
    }

    private function getSearchCriteriaResolverFactory(): SearchCriteriaResolverFactory
    {
        $ref = new \ReflectionProperty(GenericCollection::class, 'searchCriteriaResolverFactory');
        $ref->setAccessible(true);

        return $ref->getValue($this);
    }

    private function getSearch(): SearchInterface
    {
        $ref = new \ReflectionMethod(GenericCollection::class, 'getSearch');
        $ref->setAccessible(true);

        return $ref->invoke($this);
    }
}
