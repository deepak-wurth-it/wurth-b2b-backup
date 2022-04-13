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

namespace Mirasvit\Search\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Model\ScoreRule\Indexer\ScoreRuleIndexer;
use Mirasvit\Search\Repository\ScoreRuleRepository;

class ScoreRuleService
{
    private $resource;

    private $storeManager;

    private $scoreRuleRepository;

    private $request;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        ScoreRuleRepository $scoreRuleRepository,
        RequestInterface $request
    ) {
        $this->resource            = $resource;
        $this->storeManager        = $storeManager;
        $this->scoreRuleRepository = $scoreRuleRepository;
        $this->request             = $request;
    }

    public function applyScores(array $results, Request $request): array
    {
        $storeId  = $this->storeManager->getStore()->getId();
        $storeIds = [0, $storeId];
        $ruleIds = [0];// include Search Weight Virtual Rule

        foreach ($this->getApplicableScoreRules($request) as $scoreRule) {
            $ruleIds[] = $scoreRule->getId();
        }

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(['index' => $this->getIndexTable()], ['*'])
            ->where('index.store_id IN (?)', $storeIds)
            ->where('index.rule_id IN (?)', $ruleIds)
            ->where('index.product_id IN (?)', array_keys($results));

        $rows = $connection->fetchAll($select);
        $actions = [];

        foreach ($rows as $row) {
            $scoreFactor = (string) $row[ScoreRuleIndexer::SCORE_FACTOR];
            if ($scoreFactor === '+0') {
                continue;
            }

            $actions[$scoreFactor][] = $row[ScoreRuleIndexer::PRODUCT_ID];
        }

        foreach ($actions as $action => $productIds) {
            $productIds = array_filter($productIds);
            $results = $this->leadTo100($results);
            foreach ($productIds as $id) {
                $score = $results[$id];
                $score = $this->calculate($score, (string)$action);
                $results[$id] = $score;
            }
        }

        DebugService::log(\Zend_Json::encode($results), 'score_rule_applied_search_results');

        return $results;
    }

    private function calculate(float $score, string $action): float
    {
        $result = $score;
        if(preg_match('/([\+\-\*\/])(?:\s*)(\d+)/', $action, $matches) !== FALSE){
            $operator = $matches[1];

            switch($operator){
                case '+':
                    $result = $score + $matches[2];
                    break;
                case '-':
                    $result = $score - $matches[2];
                    break;
                case '*':
                    $result = $score * $matches[2];
                    break;
                case '/':
                    $result = $score / $matches[2];
                    break;
            }
        }

        return (float) $result;
    }

    private function getIndexTable(): string
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }

    private function getApplicableScoreRules(Request $request): array
    {
        $result  = [];
        $storeId = $this->storeManager->getStore()->getId();

        $query = array_values($request->getQuery()->getShould())['0'];
        if ($query instanceof \Magento\Framework\Search\Request\Query\Filter) {
            $searchQuery = $query->getReference()->getValue();
        } else {
            $searchQuery = $query->getValue();
        }
        
        $scoreRules = $this->scoreRuleRepository->getCollection()
            ->addFieldToFilter(ScoreRuleInterface::IS_ACTIVE, 1);

        /** @var ScoreRuleInterface $scoreRule */
        foreach ($scoreRules as $scoreRule) {
            if (!in_array($storeId, $scoreRule->getStoreIds())) {
                continue;
            }

            if ($scoreRule->getActiveFrom() && strtotime($scoreRule->getActiveFrom()) > time()) {
                continue;
            }

            if ($scoreRule->getActiveTo() && strtotime($scoreRule->getActiveTo()) < time()) {
                continue;
            }

            $rule = $scoreRule->getRule();
            $obj  = new \Mirasvit\Search\Model\ScoreRule\DataObject();
            $obj->setData([
                'query' => $searchQuery,
            ]);

            if (!$rule->getPostConditions()->validate($obj)) {
                continue;
            }

            $result[] = $scoreRule;
        }

        return $result;
    }

    private function leadTo100(array $results): array
    {
        $maxScore = max($results);
        if ($maxScore < 1) {
            $maxScore = 1;
        }
        foreach ($results as $id => $score) {
            $results[$id] = $score / $maxScore * 100;
        }

        return $results;
    }
}
