<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Model;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class CategoryImageProcessor
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
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Framework\App\ResourceConnection        $resourceConnection,
        \Magento\Catalog\Model\CategoryFactory           $categoryFactory,
        ObjectManagerInterface                           $objectManager,
        \Magento\Framework\File\Csv                      $fileCsv,
        \Magento\Framework\App\Filesystem\DirectoryList  $directoryList,
        \Magento\Store\Model\StoreManagerInterface       $storeManager,
        \Pim\Category\Model\PimCategoryFactory           $pimCategoryFactory,
        \Pim\Category\Model\PimCategoryImagesFactory      $pimCategoryImagesFactory,
        \Magento\Catalog\Model\CategoryRepository        $categoryRepository,
        \Pim\Category\Model\ResourceModel\PimCategoryImages $categoryImagesResource,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Pim\Category\Model\ImportImageServiceCategory  $importImageServiceCategory,


        LoggerInterface $logger


    ) {
        $this->categoryFactory = $categoryFactory;
        $this->resourceConnection = $resourceConnection;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->objectManager = $objectManager;
        $this->fileCsv = $fileCsv;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->pimCategoryFactory = $pimCategoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->pimCategoryImagesFactory = $pimCategoryImagesFactory;
        $this->categoryImagesResource = $categoryImagesResource;
        $this->indexerFactory = $indexerFactory;
        $this->importImageServiceCategory = $importImageServiceCategory;



        $this->logger = $logger;
    }


    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $this->category = $this->categoryFactory->create();
        $categoryCollection = $this->category->getCollection()
            ->setOrder('entity_id', 'ASC');

        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];


        if ($categoryCollection->getSize() && $categoryCollection->count()) {
            $i = 0;
            foreach ($categoryCollection as $key => $category) {
                //print_r(get_class_methods($this->category));
                //exit;
                $time_start = microtime(true);
                $catId = $category->getId();

                $pimCatId = $category->getData('pim_category_id');
                if ($pimCatId <= 0) {
                    continue;
                }


                $pimCategoryImageObject = $this->pimCategoryImagesFactory->create();
                $categoryImagesObject = $pimCategoryImageObject->getCollection()
                    ->addFieldToFilter('CategoryId', $pimCatId);
                if ($categoryImagesObject->getSize() && $categoryImagesObject->count()) {
                    foreach ($categoryImagesObject as $images) {

                        try {

                        $imageUrl = $images->getData('Path');
                        if ($imageUrl) {
                            $this->importImageServiceCategory->execute($category, $imageUrl, $visible = true, $imageType = ['image', 'small_image', 'thumbnail']);
                        }

                        $id = $images->getId();

                        $this->categoryImagesResource->updateByQuery($id);
                        } catch (\Exception $e) {
                          $this->logger->info(print_r($e->getMessage(), true));
                        }
                    }
                    $this->categoryRepository->save($category);
                    if ($i == 500) {
                        $i = 0;
                        $this->reindexByKey($indexLists);
                    }
                    $categoryImagePath = $category->getImageUrl();
                    if ($categoryImagePath && $imageUrl) {
                        $time_end = microtime(true);
                        $log  = "Category  Id : " . $catId . " Image Path " . $categoryImagePath . PHP_EOL;
                        $log  .= "Category took " . number_format(microtime(true) - $time_start, 2) . " seconds." . PHP_EOL;
                        $log  .= "Download images from " . $imageUrl . " Done" . PHP_EOL;
                        $this->getImageImportLogger($log);
                        echo $log;
                        $i++;
                    }
                }
            }
        }
    }

    public function getImageImportLogger($log)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/category_image_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }



    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByKey($indexLists)
    {
        echo 'Full Reindex started .....' . PHP_EOL;
        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.' . PHP_EOL;;
    }
}
