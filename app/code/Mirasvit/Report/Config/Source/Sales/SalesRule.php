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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Config\Source\Sales;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\RuleRepository;

class SalesRule implements OptionSourceInterface
{
    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SalesRule constructor.
     * @param RuleRepository $ruleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getSalesRules() as $rule) {
            $result[] = [
                'label' => $rule->getName(),
                'value' => $rule->getRuleId(),
            ];
        }

        return $result;
    }

    /**
     * @return \Magento\SalesRule\Api\Data\RuleInterface[]
     */
    public function getSalesRules()
    {
        $searchResults = $this->ruleRepository->getList($this->searchCriteriaBuilder->create());
        $salesRules = $searchResults->getItems();

        return $salesRules;
    }
}
