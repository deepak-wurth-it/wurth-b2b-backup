<?php

namespace Amasty\BannersLite\Model;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Model\BannerRuleFactory;
use Amasty\BannersLite\Model\Cache;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as BannerRuleResource;
use Amasty\BannersLite\Model\ResourceModel\BannerRule\CollectionFactory;
use Amasty\BannersLite\Model\ResourceModel\BannerRule\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BannerRuleRepository implements BannerRuleRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var BannerRuleFactory
     */
    private $bannerRuleFactory;

    /**
     * @var BannerRuleResource
     */
    private $bannerRuleResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $bannerRules;

    /**
     * @var CollectionFactory
     */
    private $bannerRuleCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        BannerRuleFactory $bannerRuleFactory,
        BannerRuleResource $bannerRuleResource,
        CollectionFactory $bannerRuleCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->bannerRuleFactory = $bannerRuleFactory;
        $this->bannerRuleResource = $bannerRuleResource;
        $this->bannerRuleCollectionFactory = $bannerRuleCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(BannerRuleInterface $bannerRule)
    {
        try {
            if ($bannerRule->getEntityId()) {
                $bannerRule = $this->getById($bannerRule->getEntityId())->addData($bannerRule->getData());
            }
            $this->bannerRuleResource->save($bannerRule);
            unset($this->bannerRules[$bannerRule->getEntityId()]);
        } catch (\Exception $e) {
            if ($bannerRule->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save bannerRule with ID %1. Error: %2',
                        [$bannerRule->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new bannerRule. Error: %1', $e->getMessage()));
        }

        return $bannerRule;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->bannerRules[$entityId])) {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleFactory->create();
            $this->bannerRuleResource->load($bannerRule, $entityId);
            if (!$bannerRule->getEntityId()) {
                throw new NoSuchEntityException(__('BannerRule with specified ID "%1" not found.', $entityId));
            }
            $this->bannerRules[$entityId] = $bannerRule;
        }

        return $this->bannerRules[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getBySalesruleId($entityId)
    {
        if (!isset($this->bannerRules[$entityId])) {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleFactory->create();
            $this->bannerRuleResource->load($bannerRule, $entityId, BannerRuleInterface::SALESRULE_ID);
            if (!$bannerRule->getEntityId()) {
                throw new NoSuchEntityException(__('BannerRule with specified ID "%1" not found.', $entityId));
            }
            $this->bannerRules[$entityId] = $bannerRule;
        }

        return $this->bannerRules[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function delete(BannerRuleInterface $bannerRule)
    {
        try {
            $this->bannerRuleResource->delete($bannerRule);
            unset($this->bannerRules[$bannerRule->getEntityId()]);
        } catch (\Exception $e) {
            if ($bannerRule->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove bannerRule with ID %1. Error: %2',
                        [$bannerRule->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove bannerRule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $bannerRuleModel = $this->getById($entityId);
        $this->delete($bannerRuleModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\BannersLite\Model\ResourceModel\BannerRule\Collection $bannerRuleCollection */
        $bannerRuleCollection = $this->bannerRuleCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $bannerRuleCollection);
        }

        $searchResults->setTotalCount($bannerRuleCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $bannerRuleCollection);
        }

        $bannerRuleCollection->setCurPage($searchCriteria->getCurrentPage());
        $bannerRuleCollection->setPageSize($searchCriteria->getPageSize());

        $bannerRules = [];
        /** @var BannerRuleInterface $bannerRule */
        foreach ($bannerRuleCollection->getItems() as $bannerRule) {
            $bannerRules[] = $this->getById($bannerRule->getEntityId());
        }

        $searchResults->setItems($bannerRules);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $bannerRuleCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $bannerRuleCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $bannerRuleCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $bannerRuleCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $bannerRuleCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $bannerRuleCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @return BannerRule
     */
    public function getEmptyModel()
    {
        return $this->bannerRuleFactory->create();
    }
}
