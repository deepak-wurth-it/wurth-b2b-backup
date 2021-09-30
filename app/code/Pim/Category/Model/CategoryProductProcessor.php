<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Model;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class CategoryProductProcessor
{
    const PIM_CATEGORIES_TABLE = 'categories';
    const PIM_MAGENTO_SYNC_STATUS = 'magento_sync_status';
    const PIM_MAGENTO_CATEGORY_ID = 'magento_category_id';
    const PIM_MAGENTO_PARENT_CATEGORY_ID = 'magento_parent_category_id';
    const MAGENTO_ROOT_CATEGORY_ID = '2';
    const FAILED_MESSAGE = 'Failed to create category Pim Id';
    const SUCCESS_MESSAGE = 'Category Sync/Build Has been done';
    const WRONG_PIM_COLLECTION_MESSAGE = 'Something went wrong in pim collection ';
    const START_MESSAGE = 'Start Import For Pim Category Id  ->>';
    const SAVE_FAILED = 'Could Not Save ';
    protected $category;

    /**
     *
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface                $categoryRepositoryInterface,
        \Magento\Framework\App\ResourceConnection                       $resourceConnection,
        \Magento\Catalog\Model\CategoryFactory                          $categoryFactory,
        ObjectManagerInterface                                          $objectManager,
        \Magento\Framework\File\Csv                                     $fileCsv,
        \Magento\Framework\App\Filesystem\DirectoryList                 $directoryList,
        \Magento\Store\Model\StoreManagerInterface                      $storeManager,
        \Pim\Category\Model\PimCategoryFactory                          $pimCategoryFactory,
        \Magento\Catalog\Model\CategoryRepository                       $categoryRepository,
        LoggerInterface                                                 $logger,
        \Pim\Category\Model\PimProductsCategoriesFactory                $pimProductsCategoriesFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement


    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->resourceConnection = $resourceConnection;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->objectManager = $objectManager;
        $this->fileCsv = $fileCsv;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->pimCategoryFactory = $pimCategoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->pimProductsCategoriesFactory = $pimProductsCategoriesFactory;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
    }


    public function initExecution()
    {

        try {
            //$collection = $this->pimProductCategoryCollection();
            //$dataArray = $collection->getData();
            //$dataArray = $this->buildCategoryTree($dataArray);
            //$this->executeTree($dataArray);
            $this->updateProductCategoryData();
        } catch (\Exception $e) {
            echo self::WRONG_PIM_COLLECTION_MESSAGE . PHP_EOL;
            echo $e->getMessage() . "\n" . PHP_EOL;
        }
    }


    public function showExceptionMessage($e, $row = null, $customMessage = null)
    {
        if ($row) {
            echo $customMessage . $row['Id'] . PHP_EOL;
        }
        echo $e->getMessage() . "\n" . PHP_EOL;
    }

    public function pimProductCategoryCollection()
    {

        $pimProductsCategoriesCollection = $this->pimProductsCategoriesFactory->create();
        return $pimProductsCategoriesCollection->getCollection()
            ->addFieldToFilter('magento_sync_status', ['null' => true])
            ->addFieldToFilter('Active', ['eq' => '1'])
            ->setOrder('CategoryId', 'DESC');


    }

    public function updateProductCategoryData()
    {

        $collection = $this->pimProductCategoryCollection();
        //echo $collection->getSelect();exit;
        foreach ($collection as $data) {
            try {
                $productObj = $this->productRepository->get($data['ProductId']);
                $categoryObj = $this->categoryRepository->getByPimCategoryId($data['CategoryId']);
                if ($productObj && $categoryObj) {
                    $catId = $categoryObj->getFirstItem()->getId();
                    $sku = $productObj->getSku();
                    $this->categoryLinkManagement->assignProductToCategories(
                        $sku,
                        [$catId]
                    );

                    $data->setData('magento_sync_status', '1');
                    $data->save();
                    echo 'Product sku ' . $sku . ' assigned  with category ' . $catId . PHP_EOL;;

                }


            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                $this->logger->info('Product not assigned to category : '. $e->getMessage());
                continue;
            }
        }
        echo 'Product has been assigned to categories' . PHP_EOL;

    }

    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // set pagination
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }
}
