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



namespace Mirasvit\Search\Plugin\Frontend;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\Request;
use Magento\Framework\Search\Response\QueryResponse;
use Mirasvit\Search\Service\ScoreRuleService;

/**
 * @see \Magento\Framework\Search\AdapterInterface::query()
 */
class ScoreRuleApplyPlugin
{
    private $scoreRuleService;

    public function __construct(
        ScoreRuleService $scoreRuleService
    ) {
        $this->scoreRuleService = $scoreRuleService;
    }

    public function afterQuery(AdapterInterface $subject, QueryResponse $response, Request $request): QueryResponse
    {
        /** @var QueryResponse $response */
        if (empty($request->getQuery()->getShould())) {
            return $response;
        }

        $originalScores = [];
        foreach ($response->getIterator() as $item) {
            $originalScores[$item->getId()] = $item->getCustomAttribute('score')->getValue();
        }

        $results = $this->scoreRuleService->applyScores($originalScores, $request);

        // if results are sorted by some value and then by relevance and scores are same
        if ($originalScores === $results) {
            return $response;
        }

        $documents = [];
        foreach ($response->getIterator() as $item) {
            $item->getCustomAttribute('score')->setValue($results[$item->getId()]);
            $documents[] = $item;
        }

        usort($documents, function ($a, $b) {
            if ($a->getCustomAttribute('score')->getValue() == $b->getCustomAttribute('score')->getValue()) {
                return 0;
            }

            return ($a->getCustomAttribute('score')->getValue() < $b->getCustomAttribute('score')->getValue()) ? 1 : -1;
        });

        return new QueryResponse(
            $documents,
            $response->getAggregations(),
            $response->getTotal()
        );
    }
}
