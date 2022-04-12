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



namespace Mirasvit\SearchSphinx\SearchAdapter;

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;
use Mirasvit\SearchSphinx\Model\Engine;
use Mirasvit\SearchSphinx\SearchAdapter\Query\Builder\MatchQuery;
use Mirasvit\SearchSphinx\SearchAdapter\Query\QueryContainer;
use Mirasvit\SearchSphinx\SearchAdapter\Query\QueryContainerFactory;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;
use Mirasvit\Search\Service\DebugService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperQL
{

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var IndexRepository
     */
    private $indexRepository;

    /**
     * @var MatchQuery
     */
    private $matchBuilder;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    private $indexNameResolver;

    public function __construct(
        IndexRepository $indexRepository,
        QueryContainerFactory $queryContainerFactory,
        MatchQuery $matchBuilder,
        IndexScopeResolver $scopeResolver,
        IndexNameResolver $indexNameResolver,
        Engine $engine
    ) {
        $this->queryContainerFactory = $queryContainerFactory;
        $this->matchBuilder          = $matchBuilder;
        $this->indexRepository       = $indexRepository;
        $this->scopeResolver         = $scopeResolver;
        $this->indexNameResolver     = $indexNameResolver;
        $this->engine                = $engine;
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    public function buildQuery(RequestInterface $request)
    {
        $searchIndex = $this->indexRepository->getByIdentifier($request->getIndex());

        $indexName = $this->indexNameResolver->getIndexName(
            $searchIndex->getIdentifier(),
            $request->getDimensions()
        );

        $weights = [];
        foreach ($this->indexRepository->getInstance($searchIndex)->getAttributeWeights() as $attr => $weight) {
            if (!is_numeric(substr($attr, 0, 1))) {
                $weights[$attr] = pow(2, (int)$weight);
            }
        }

        $sphinxQuery = $this->engine->getQuery()
            ->select(["id", new QLExpression("weight()")])
            ->from($indexName)
            ->limit(0, 1000000)
            ->option("max_matches", 1000000)
            ->option("field_weights", $weights)
            ->option("ranker", new QLExpression("expr('sum(1/min_hit_pos*user_weight
                + word_count*user_weight + exact_hit*user_weight*1000 + lcs*user_weight) * 1000 + bm25')"));

        $queryContainer = $this->queryContainerFactory->create(['request' => $request]);

        $sphinxQuery = $this->processQuery(
            $request->getQuery(),
            $sphinxQuery,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $sphinxQuery = $this->addDerivedQueries(
            $queryContainer,
            $sphinxQuery
        );

        $result = $sphinxQuery->execute();

        DebugService::log($sphinxQuery->getCompiled(), 'search_query');
        DebugService::log(\Zend_Json::encode($result), 'search_results');

        $pairs = [];
        foreach ($result as $item) {
            $pairs[$item['id']] = $item['weight()'];
        }

        return $pairs;
    }

    /**
     * @param RequestQueryInterface $query
     * @param SphinxQL              $select
     * @param string                $conditionType
     * @param QueryContainer        $queryContainer
     *
     * @return SphinxQL
     */
    private function processQuery(
        RequestQueryInterface $query,
        SphinxQL $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                $select = $queryContainer->addMatchQuery(
                    $select,
                    $query,
                    $conditionType
                );
                break;

            case RequestQueryInterface::TYPE_BOOL:
                $select = $this->processBoolQuery(
                    $query,
                    $select,
                    $queryContainer
                );
                break;
        }

        return $select;
    }

    /**
     * @param BoolQuery      $query
     * @param SphinxQL       $select
     * @param QueryContainer $queryContainer
     *
     * @return SphinxQL
     */
    private function processBoolQuery(
        BoolQuery $query,
        SphinxQL $select,
        QueryContainer $queryContainer
    ) {
        $select = $this->processBoolQueryCondition(
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT,
            $queryContainer
        );

        return $select;
    }

    /**
     * @param array          $subQueryList
     * @param SphinxQL       $select
     * @param string         $conditionType
     * @param QueryContainer $queryContainer
     *
     * @return SphinxQL
     */
    private function processBoolQueryCondition(
        array $subQueryList,
        SphinxQL $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($subQuery, $select, $conditionType, $queryContainer);
        }

        return $select;
    }

    /**
     * @param QueryContainer $queryContainer
     * @param SphinxQL       $select
     *
     * @return SphinxQL
     */
    private function addDerivedQueries(
        QueryContainer $queryContainer,
        SphinxQL $select
    ) {
        $matchQueries = $queryContainer->getMatchQueries();

        if ($matchQueries) {
            $matchContainer = array_shift($matchQueries);

            $select = $this->matchBuilder->build(
                $select,
                $matchContainer->getRequest()
            );
        }

        return $select;
    }
}
