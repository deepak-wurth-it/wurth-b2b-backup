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


declare(strict_types=1);

namespace Mirasvit\SearchElastic\Plugin\Frontend;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as Mapper;
use Mirasvit\SearchElastic\Plugin\PutScoreBoostBeforeAddDocsPlugin as ScoreBoostProcessor ;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Repository\ScoreRuleRepository;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Query\Builder as QueryBuilder;
use Mirasvit\SearchElastic\SearchAdapter\Query\Builder\MatchCompatibility as MatchQueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;

/**
 * @see \Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper::buildQuery()
 */
class ElasticsearchAddScriptToSearchQueryPlugin extends Mapper
{
    protected $queryBuilder;

    protected $matchQueryBuilder;

    protected $filterBuilder;

    protected $scoreRuleRepository;

    public function __construct(
        QueryBuilder $queryBuilder,
        MatchQueryBuilder $matchQueryBuilder,
        FilterBuilder $filterBuilder,
        ScoreRuleRepository $scoreRuleRepository
    ) {
        $this->scoreRuleRepository = $scoreRuleRepository;
        parent::__construct($queryBuilder, $matchQueryBuilder, $filterBuilder);
    }

    public function aroundBuildQuery(Mapper $subject, callable $proceed, RequestInterface $request)
    {
        $searchQuery = $proceed($request);

        if ($request->getQuery()->getName() == 'quick_search_container'
            && $this->scoreRuleRepository->getCollection()->getSize() > 0
        ) {
            $searchQuery['body']['query']['script_score']['query'] = $searchQuery['body']['query'];
            $searchQuery['body']['query']['script_score']['script'] = [
                'source' => '_score * doc[\''. ScoreBoostProcessor::MULTIPLY_ATTRIBUTE .'\'].value'.
                    ' + doc[\''. ScoreBoostProcessor::SUM_ATTRIBUTE .'\'].value'
            ];

            unset($searchQuery['body']['query']['bool']);
        }

        return $searchQuery;
    }
}
