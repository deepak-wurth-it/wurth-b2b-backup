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



namespace Mirasvit\Search\Model\Index;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Repository\IndexRepository;

class Context
{
    private $indexer;

    private $searcher;

    private $resourceConnection;

    private $objectManager;

    private $storeManager;

    private $config;

    private $contextFactory;

    private $indexRepository;

    private $request;

    public function __construct(
        IndexerFactory $indexerFactory,
        SearcherFactory $searcherFactory,
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ConfigProvider $config,
        ContextFactory $contextFactory,
        IndexRepository $indexRepository,
        RequestInterface $request
    ) {
        $this->indexer            = $indexerFactory->create();
        $this->searcher           = $searcherFactory->create();
        $this->resourceConnection = $resourceConnection;
        $this->objectManager      = $objectManager;
        $this->storeManager       = $storeManager;
        $this->config             = $config;
        $this->contextFactory     = $contextFactory;
        $this->indexRepository    = $indexRepository;
        $this->request            = $request;
    }

    /**
     * @return Context
     */
    public function getInstance()
    {
        return $this->contextFactory->create();
    }

    /**
     * @return Indexer
     */
    public function getIndexer()
    {
        return $this->indexer;
    }

    /**
     * @return Searcher
     */
    public function getSearcher()
    {
        return $this->searcher;
    }

    /**
     * @return ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return ConfigProvider
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return IndexRepository
     */
    public function getIndexRepository()
    {
        return $this->indexRepository;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
