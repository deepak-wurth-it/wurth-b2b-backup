<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Wcb\ApiConnect\Api\Data\SoapClientInterface;
use Wcb\ApiConnect\Api\Data\SoapClientInterfaceFactory;
use Wcb\ApiConnect\Api\Data\SoapClientSearchResultsInterfaceFactory;
use Wcb\ApiConnect\Api\SoapClientRepositoryInterface;
use Wcb\ApiConnect\Model\ResourceModel\SoapClient as ResourceSoapClient;
use Wcb\ApiConnect\Model\ResourceModel\SoapClient\CollectionFactory as SoapClientCollectionFactory;

class SoapClientRepository implements SoapClientRepositoryInterface
{

    /**
     * @var ResourceSoapClient
     */
    protected $resource;

    /**
     * @var SoapClientInterfaceFactory
     */
    protected $soapClientFactory;

    /**
     * @var SoapClientCollectionFactory
     */
    protected $soapClientCollectionFactory;

    /**
     * @var SoapClient
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceSoapClient $resource
     * @param SoapClientInterfaceFactory $soapClientFactory
     * @param SoapClientCollectionFactory $soapClientCollectionFactory
     * @param SoapClientSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSoapClient $resource,
        SoapClientInterfaceFactory $soapClientFactory,
        SoapClientCollectionFactory $soapClientCollectionFactory,
        SoapClientSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->soapClientFactory = $soapClientFactory;
        $this->soapClientCollectionFactory = $soapClientCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(SoapClientInterface $soapClient)
    {
        try {
            $this->resource->save($soapClient);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the soapClient: %1',
                $exception->getMessage()
            ));
        }
        return $soapClient;
    }

    /**
     * @inheritDoc
     */
    public function get($soapClientId)
    {
        $soapClient = $this->soapClientFactory->create();
        $this->resource->load($soapClient, $soapClientId);
        if (!$soapClient->getId()) {
            throw new NoSuchEntityException(__('SoapClient with id "%1" does not exist.', $soapClientId));
        }
        return $soapClient;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->soapClientCollectionFactory->create();
        
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
    public function delete(SoapClientInterface $soapClient)
    {
        try {
            $soapClientModel = $this->soapClientFactory->create();
            $this->resource->load($soapClientModel, $soapClient->getSoapclientId());
            $this->resource->delete($soapClientModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the SoapClient: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($soapClientId)
    {
        return $this->delete($this->get($soapClientId));
    }
}

