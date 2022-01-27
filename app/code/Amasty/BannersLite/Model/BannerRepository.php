<?php

namespace Amasty\BannersLite\Model;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Model\BannerFactory;
use Amasty\BannersLite\Model\ResourceModel\Banner as BannerResource;
use Amasty\BannersLite\Model\ResourceModel\Banner\CollectionFactory;
use Amasty\BannersLite\Model\ResourceModel\Banner\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
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
class BannerRepository implements BannerRepositoryInterface
{
    const BANNERS_COUNT = 3;

    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * @var BannerResource
     */
    private $bannerResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $banners;

    /**
     * @var CollectionFactory
     */
    private $bannerCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource,
        CollectionFactory $bannerCollectionFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->bannerFactory = $bannerFactory;
        $this->bannerResource = $bannerResource;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(BannerInterface $banner)
    {
        try {
            if ($banner->getEntityId()) {
                $banner = $this->getById($banner->getEntityId())->addData($banner->getData());
            }
            $this->bannerResource->save($banner);
            unset($this->banners[$banner->getEntityId()]);
        } catch (\Exception $e) {
            if ($banner->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save banner with ID %1. Error: %2',
                        [$banner->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new banner. Error: %1', $e->getMessage()));
        }

        return $banner;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->banners[$entityId])) {
            /** @var \Amasty\BannersLite\Model\Banner $banner */
            $banner = $this->bannerFactory->create();
            $this->bannerResource->load($banner, $entityId);
            if (!$banner->getEntityId()) {
                throw new NoSuchEntityException(__('Banner with specified ID "%1" not found.', $entityId));
            }
            $this->banners[$entityId] = $banner;
        }

        return $this->banners[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getBySalesruleId($ruleId)
    {
        /** @var \Magento\Framework\Api\SearchCriteria $criteria */
        $criteria = $this->criteriaBuilder->addFilter(
            BannerInterface::SALESRULE_ID,
            $ruleId
        )->create();

        $items = $this->getList($criteria)->getItems();

        if ($items) {
            return $items;
        } else {
            return $this->defaultModels();
        }
    }

    /**
     * @return array
     */
    private function defaultModels()
    {
        $banners = [];

        for ($bannerNumber = 1; $bannerNumber <= self::BANNERS_COUNT; $bannerNumber++) {
            $banners[] = $this->bannerFactory->create();
        }

        return $banners;
    }

    /**
     * @inheritdoc
     */
    public function getByBannerType($ruleId, $bannerType)
    {
        $item = $this->bannerCollectionFactory->create()
           ->addFieldToFilter(BannerInterface::SALESRULE_ID, $ruleId)
           ->addFieldToFilter(BannerInterface::BANNER_TYPE, $bannerType)
           ->getFirstItem();

        if ($item) {
            return $item;
        } else {
            throw new NoSuchEntityException(__('Banner with specified ID and Banner Type not found.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(BannerInterface $banner)
    {
        try {
            $this->bannerResource->delete($banner);
            unset($this->banners[$banner->getEntityId()]);
        } catch (\Exception $e) {
            if ($banner->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove banner with ID %1. Error: %2',
                        [$banner->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove banner. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $bannerModel = $this->getById($entityId);
        $this->delete($bannerModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\BannersLite\Model\ResourceModel\Banner\Collection $bannerCollection */
        $bannerCollection = $this->bannerCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $bannerCollection);
        }

        $searchResults->setTotalCount($bannerCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $bannerCollection);
        }

        $bannerCollection->setCurPage($searchCriteria->getCurrentPage());
        $bannerCollection->setPageSize($searchCriteria->getPageSize());

        $banners = [];
        /** @var BannerInterface $banner */
        foreach ($bannerCollection->getItems() as $banner) {
            $banners[] = $this->getById($banner->getEntityId());
        }

        $searchResults->setItems($banners);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $bannerCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $bannerCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $bannerCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $bannerCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $bannerCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $bannerCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @return Banner
     */
    public function getEmptyModel()
    {
        return $this->bannerFactory->create();
    }
}
