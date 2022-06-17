<?php

namespace Wcb\MirasavitSearchAutocomplate\Plugin\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Search\Model\Query;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;

class ConfigProvider
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;
    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * ConfigProvider constructor.
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param UserContextInterface $userContext
     */
    public function __construct(
        QueryCollectionFactory $queryCollectionFactory,
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
        $this->queryCollectionFactory = $queryCollectionFactory;
    }

    public function aroundGetPopularSearches(\Mirasvit\SearchAutocomplete\Model\ConfigProvider $subject, callable $proceed)
    {
        $result = $subject->getDefaultPopularSearches();

        if (!count($result)) {
            $ignored = $subject->getIgnoredSearches();

            $collection = $this->queryCollectionFactory->create()
                ->setPopularQueryFilter()
                ->setPageSize(6);

            if ($this->getCustomerId()) {
                $collection = $this->queryCollectionFactory->create()
                    ->setPageSize(6);
                $collection->addFieldToFilter('customer_id', ['eq' => $this->getCustomerId()]);
                $collection->setOrder('query_id', 'desc');
            }

            /** @var Query $query */
            foreach ($collection as $query) {
                $text = $query->getQueryText();
                $isIgnored = false;
                foreach ($ignored as $word) {
                    if (strpos(strtolower($text), $word) !== false) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $result[] = mb_strtolower($text);
                }
            }
        }

        $result = array_slice(array_unique($result), 0, $subject->getPopularLimit());
        $result = array_map('ucfirst', $result);

        return $result;
    }

    public function getCustomerId()
    {
        return $this->userContext->getUserId();
    }
}
