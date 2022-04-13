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



namespace Mirasvit\Search\Index;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\Search\Service\ContentService;

class Context
{
    private $resourceConnection;

    private $objectManager;

    private $storeManager;

    private $configProvider;

    private $contextFactory;

    private $indexRepository;

    private $contentService;

    private $request;

    public function __construct(
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        IndexRepository $indexRepository,
        ContentService $contentService,
        RequestInterface $request
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->objectManager      = $objectManager;
        $this->storeManager       = $storeManager;
        $this->configProvider     = $configProvider;
        $this->indexRepository    = $indexRepository;
        $this->contentService     = $contentService;
        $this->request            = $request;
    }

    public function getInstance()
    {
        return $this->contextFactory->create();
    }

    public function getResource()
    {
        return $this->resourceConnection;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getStoreManager()
    {
        return $this->storeManager;
    }

    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    public function getIndexRepository()
    {
        return $this->indexRepository;
    }

    public function getContentService()
    {
        return $this->contentService;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
