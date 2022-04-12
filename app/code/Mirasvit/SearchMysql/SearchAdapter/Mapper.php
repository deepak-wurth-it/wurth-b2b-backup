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



namespace Mirasvit\SearchMysql\SearchAdapter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\MatchQuery as MatchQueryBuilder;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

class Mapper
{
    private $queryContainerFactory;

    private $scoreBuilderFactory;

    private $filterBuilder;

    private $matchBuilder;

    private $sorter;

    private $indexProviders = [];

    public function __construct(
        Query\QueryContainerFactory $queryContainerFactory,
        ScoreBuilderFactory $scoreBuilderFactory,
        Filter\Builder $filterBuilder,
        MatchCompatibility $matchBuilder,
        Query\Sorter $sorter,
        array $indexProviders = []
    ) {
        $this->queryContainerFactory = $queryContainerFactory;
        $this->scoreBuilderFactory   = $scoreBuilderFactory;
        $this->filterBuilder         = $filterBuilder;
        $this->matchBuilder          = $matchBuilder;
        $this->sorter                = $sorter;
        $this->indexProviders        = $indexProviders;
    }

    public function buildQuery(RequestInterface $request): Select
    {
        if (!isset($this->indexProviders[$request->getIndex()])) {
            throw new \Exception('Index provider not configured for ' . $request->getIndex());
        }

        $indexBuilder = $this->indexProviders[$request->getIndex()];

        $queryContainer = $this->queryContainerFactory->create([
            'indexBuilder' => $indexBuilder,
            'request'      => $request,
        ]);

        $select = $indexBuilder->build($request);

        $scoreBuilder = $this->scoreBuilderFactory->create();

        $select = $this->processQuery(
            $scoreBuilder,
            $request->getQuery(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->addDerivedQueries(
            $request,
            $queryContainer,
            $scoreBuilder,
            $select,
            $indexBuilder
        );

        $this->sorter->process($select, $request);
        $select->order('search_index.entity_id ASC');

        return $select;
    }

    protected function processQuery(
        ScoreBuilder $scoreBuilder,
        RequestQueryInterface $query,
        Select $select,
        string $conditionType,
        Query\QueryContainer $queryContainer
    ): Select {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQueryBuilder $query */
                $select = $queryContainer->addMatchQuery(
                    $select,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $query */
                $select = $this->processBoolQuery($scoreBuilder, $query, $select, $queryContainer);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $query */
                $select = $this->processFilterQuery($scoreBuilder, $query, $select, $conditionType, $queryContainer);
                break;
            default:
                throw new \Exception(sprintf('Unknown query type \'%s\'', $query->getType()));
        }

        return $select;
    }


    private function processBoolQuery(
        ScoreBuilder $scoreBuilder,
        BoolQuery $query,
        Select $select,
        Query\QueryContainer $queryContainer
    ): Select {
        $scoreBuilder->startQuery();

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT,
            $queryContainer
        );

        $scoreBuilder->endQuery((int)$query->getBoost());

        return $select;
    }

    private function processBoolQueryCondition(
        ScoreBuilder $scoreBuilder,
        array $subQueryList,
        Select $select,
        string $conditionType,
        Query\QueryContainer $queryContainer
    ): Select {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($scoreBuilder, $subQuery, $select, $conditionType, $queryContainer);
        }

        return $select;
    }

    private function processFilterQuery(
        ScoreBuilder $scoreBuilder,
        FilterQuery $query,
        Select $select,
        string $conditionType,
        Query\QueryContainer $queryContainer
    ): Select {
        $scoreBuilder->startQuery();
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $select = $this->processQuery(
                    $scoreBuilder,
                    $query->getReference(),
                    $select,
                    $conditionType,
                    $queryContainer
                );
                $scoreBuilder->endQuery((int)$query->getBoost());
                break;

            case FilterQuery::REFERENCE_FILTER:
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType);

                if ($filterCondition) {
                    $select->where($filterCondition);
                }
                break;
        }
        $scoreBuilder->endQuery((int)$query->getBoost());

        return $select;
    }

    private function addDerivedQueries(
        RequestInterface $request,
        Query\QueryContainer $queryContainer,
        ScoreBuilder $scoreBuilder,
        Select $select,
        Mapper\IndexBuilder $indexBuilder
    ): Select {
        $matchQueries = $queryContainer->getMatchQueries();

        if (!$matchQueries) {
            $select->columns($scoreBuilder->build());
        } else {
            $matchContainer = array_shift($matchQueries);

            $this->matchBuilder->build(
                $scoreBuilder,
                $select,
                $matchContainer->getRequest(),
                $matchContainer->getConditionType()
            );

            $select->columns($scoreBuilder->build());
        }

        return $select;
    }
}
