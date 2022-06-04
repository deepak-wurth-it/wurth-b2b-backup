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
    const PIM_PRODUCT_CATEGORIES = 'productscategories';
    protected $category;
    protected $productscategoriesUpdateObj;
    protected $sku;

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
        \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement,
        \Magento\Catalog\Model\ProductFactory                           $productFactory,
        \Magento\Indexer\Model\IndexerFactory                           $indexerFactory



    ) {
        $this->categoryFactory = $categoryFactory;
        $this->resourceConnection = $resourceConnection;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->objectManager = $objectManager;
        $this->fileCsv = $fileCsv;
        $this->productFactory = $productFactory;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->pimCategoryFactory = $pimCategoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
        $this->pimProductsCategoriesFactory = $pimProductsCategoriesFactory;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
    }


    public function initExecution()
    {

        try {
            $this->updateProductCategoryData();
        } catch (\Exception $e) {
            echo self::WRONG_PIM_COLLECTION_MESSAGE . PHP_EOL;
            echo $e->getMessage() . "\n" . PHP_EOL;
        }
    }


    public function showExceptionMessage($e, $sku, $customMessage = null)
    {
        if ($sku) {
            echo $customMessage .$sku . PHP_EOL;
        }
        echo $e->getMessage() . "\n" . PHP_EOL;
    }

    public function pimProductCategoryCollection($sku = null)
    {

        $pimProductsCategoriesCollection = $this->pimProductsCategoriesFactory->create();
        $pimProductsCategoriesCollection = $pimProductsCategoriesCollection->getCollection();
        if ($sku) {
            $pimProductsCategoriesCollection = $pimProductsCategoriesCollection->addFieldToFilter('ProductId', ['eq' => $sku]);
        }
        $pimProductsCategoriesCollection->addFieldToSelect('CategoryId')
            ->addFieldToFilter('UpdateRequired', ['eq' => '1'])
            ->addFieldToFilter('Active', ['eq' => '1']);

        $pimProductsCategoriesCollection->getSelect()->order('ProductId ASC');

        return $pimProductsCategoriesCollection;
    }


    //Pim Category Collection
    public function pimProductCategoryPlainCollection()
    {

        $pimProductsCategoriesCollection = $this->pimProductsCategoriesFactory->create();
        $pimProductsCategoriesCollection = $pimProductsCategoriesCollection->getCollection();

        return $pimProductsCategoriesCollection;
    }



    // Specific Product Collection from Pim Categories
    public function getProductCountInPimCategoryTable($sku = null)
    {
        $rowCollection = $this->pimProductCategoryPlainCollection();
        if ($sku) {
            $rowCollection = $rowCollection->addFieldToFilter('ProductId', ['eq' => $sku]);
        }
        $rowCollection = $rowCollection->addFieldToSelect('CategoryId')->addFieldToFilter('Active', ['eq' => '1']);
        $pimCategoryIds = $rowCollection->getData('CategoryId');
        return $pimCategoryIds;
    }

    // Get magento Product Collection
    public function getProductCollection()
    {
        $collection = $this->productFactory->create()->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        return $collection;
    }

    //Get Magento categories
    public function getMagentoCategoriesID($pimCategoryIds = null)
    {
        //Get Magento Category ids 
        $magentoCategoriesIds = $this->categoryFactory->create();
        $collectionMagentoCategory = $magentoCategoriesIds->getCollection()->addAttributeToSelect('entity_id');
        if ($pimCategoryIds) {
            $collectionMagentoCategory->addAttributeToFilter('pim_category_id', array('in' => $pimCategoryIds));
        }
        $collectionMagentoCategory->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collectionMagentoCategory->getSelect()->columns('entity_id');

        $collectionIds = $collectionMagentoCategory->getAllIds();

        return $collectionIds;
    }




    public function updateProductCategoryData()
    {
        $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

        $products = $this->getProductCollection();
        $i=0;
        foreach ($products as $row) {
            try {

                //Get Sku    
                $this->sku = $row->getSku();
                $sku =  $this->sku;
                //$sku = '1733';

                //Get product occurance in pim product categories db
                $pimProductCategoryCollection = $this->pimProductCategoryCollection($sku);

                if ($pimProductCategoryCollection->getSize()) {
                    $pimCategories =  implode(",", array_column($pimProductCategoryCollection->getData(), "CategoryId"));

                    if ($pimCategories) {

                        $collectionIds = $this->getMagentoCategoriesID($pimCategories);

                        if (is_array($collectionIds)) {

                            $collectionIds = implode(",", $collectionIds);
                        }
                        if ($collectionIds) {
                            $row->setCategoryIds($collectionIds);
                            $row->save();
                            $log = 'Categories '. $collectionIds.' assigned to sku  ' . $sku . PHP_EOL;
                            $i++;

                            // $this->categoryLinkManagement->assignProductToCategories(
                            //     $sku,
                            //     [$collectionIds]
                            // );

                            // $log .= 'Product sku ' . $sku . ' assigned  with category ' . $collectionIds . PHP_EOL;
                            $this->updateRow($sku);
                            echo $log;
                        }
                        if($i == 25){
                            $i=0;
                            $this->reindexByKey($indexLists);
                        }
                    }
                }
            } catch (\Exception $e) {

                $this->logger->info(self::SAVE_FAILED . ' For Sku : ' . $this->sku . PHP_EOL);
                $this->showExceptionMessage($e, $this->sku,self::SAVE_FAILED);
                $this->logger->info('Not Assigned category for sku : ' . $this->sku);
                $this->logger->info($e->getMessage());
                continue;
            }
        }
        echo 'Product has been assigned to categories' . PHP_EOL;
    }


    public function updateRow($sku)
    {
        if(empty($this->productscategoriesUpdateObj)){
        $this->productscategoriesUpdateObj = $this->pimProductsCategoriesFactory->create();
        }
        $connection  = $this->productscategoriesUpdateObj->getResource()->getConnection();

        $data = ["UpdateRequired" => (int)0]; // you can use as per your requirement

        $where = ['ProductId = ?' => (int)$sku];
        $tableName = $connection->getTableName(self::PIM_PRODUCT_CATEGORIES);

        $updatedRows = $connection->update($tableName, $data, $where);

        echo "Updated Rows : " . $updatedRows . PHP_EOL;
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

     /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByKey($indexLists){
        echo 'Full Reindex started .....'.PHP_EOL;
        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
        echo 'Full Reindex Done.'.PHP_EOL;;
    }
}
