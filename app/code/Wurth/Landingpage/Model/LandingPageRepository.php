<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wurth\Landingpage\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Wurth\Landingpage\Api\Data\LandingPageInterface;
use Wurth\Landingpage\Api\Data\LandingPageInterfaceFactory;
use Wurth\Landingpage\Api\Data\LandingPageSearchResultsInterfaceFactory;
use Wurth\Landingpage\Api\LandingPageRepositoryInterface;
use Wurth\Landingpage\Model\ResourceModel\LandingPage as ResourceLandingPage;
use Wurth\Landingpage\Model\ResourceModel\LandingPage\CollectionFactory as LandingPageCollectionFactory;

class LandingPageRepository implements LandingPageRepositoryInterface
{

    /**
     * @var LandingPageCollectionFactory
     */
    protected $landingPageCollectionFactory;

    /**
     * @var LandingPageInterfaceFactory
     */
    protected $landingPageFactory;

    /**
     * @var ResourceLandingPage
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var LandingPage
     */
    protected $searchResultsFactory;


    /**
     * @param ResourceLandingPage $resource
     * @param LandingPageInterfaceFactory $landingPageFactory
     * @param LandingPageCollectionFactory $landingPageCollectionFactory
     * @param LandingPageSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceLandingPage $resource,
        LandingPageInterfaceFactory $landingPageFactory,
        LandingPageCollectionFactory $landingPageCollectionFactory,
        LandingPageSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->landingPageFactory = $landingPageFactory;
        $this->landingPageCollectionFactory = $landingPageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(LandingPageInterface $landingPage)
    {
        try {
            $this->resource->save($landingPage);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the landingPage: %1',
                $exception->getMessage()
            ));
        }
        return $landingPage;
    }

    /**
     * @inheritDoc
     */
    public function get($landingPageId)
    {
        $landingPage = $this->landingPageFactory->create();
        $this->resource->load($landingPage, $landingPageId);
        if (!$landingPage->getId()) {
            throw new NoSuchEntityException(__('landing_page with id "%1" does not exist.', $landingPageId));
        }
        return $landingPage;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->landingPageCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(LandingPageInterface $landingPage)
    {
        try {
            $landingPageModel = $this->landingPageFactory->create();
            $this->resource->load($landingPageModel, $landingPage->getLandingPageId());
            $this->resource->delete($landingPageModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the landing page: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($landingPageId)
    {
        return $this->delete($this->get($landingPageId));
    }
}
