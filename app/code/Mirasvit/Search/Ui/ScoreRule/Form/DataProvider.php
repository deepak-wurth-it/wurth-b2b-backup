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



namespace Mirasvit\Search\Ui\ScoreRule\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Repository\ScoreRuleRepository;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        ScoreRuleRepository $repository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $repository->getCollection();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $result = [];

        foreach ($this->collection as $scoreRule) {
            [$p, $factor, $factorRelatively] = explode('|', $scoreRule->getScoreFactor());

            $factorType = ($p == '*' || $p == '+') ? '+' : '-';
            $factorUnit = ($p == '*' || $p == '/') ? '*' : '+';

            $data = [
                ScoreRuleInterface::ID          => $scoreRule->getId(),
                ScoreRuleInterface::TITLE       => $scoreRule->getTitle(),
                ScoreRuleInterface::IS_ACTIVE   => $scoreRule->isActive(),
                ScoreRuleInterface::ACTIVE_FROM => $scoreRule->getActiveFrom(),
                ScoreRuleInterface::ACTIVE_TO   => $scoreRule->getActiveTo(),
                ScoreRuleInterface::STORE_IDS   => $scoreRule->getStoreIds(),

                ScoreRuleInterface::SCORE_FACTOR                 => $factor,
                ScoreRuleInterface::SCORE_FACTOR . '_type'       => $factorType,
                ScoreRuleInterface::SCORE_FACTOR . '_unit'       => $factorUnit,
                ScoreRuleInterface::SCORE_FACTOR . '_relatively' => $factorRelatively,
            ];

            $result[$scoreRule->getId()] = $data;
        }

        return $result;
    }
}
