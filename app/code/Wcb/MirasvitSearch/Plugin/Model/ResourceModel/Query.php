<?php

namespace Wcb\MirasvitSearch\Plugin\Model\ResourceModel;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Search\Model\Query as QueryModel;
use Zend_Db_Expr;

class Query
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Query constructor.
     * @param UserContextInterface $userContext
     */
    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * @param \Magento\Search\Model\ResourceModel\Query $subject
     * @param callable $proceed
     * @param QueryModel $query
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundSaveIncrementalPopularity(\Magento\Search\Model\ResourceModel\Query $subject, callable $proceed, QueryModel $query)
    {
        $adapter = $subject->getConnection();
        $table = $subject->getMainTable();
        $saveData = [
            'store_id' => $query->getStoreId(),
            'query_text' => $query->getQueryText(),
            'popularity' => 1,
            'customer_id' => $this->getCustomerId()
        ];
        $updateData = [
            'popularity' => new Zend_Db_Expr('`popularity` + 1'),
        ];

        $adapter->insertOnDuplicate($table, $saveData, $updateData);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->userContext->getUserId();
    }
}
