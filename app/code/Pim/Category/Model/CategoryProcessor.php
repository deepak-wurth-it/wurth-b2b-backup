<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Model;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
class CategoryProcessor
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
        \Magento\Catalog\Model\CategoryRepository        $categoryRepository,
        LoggerInterface $logger


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
    }


    public function initExecution()
    {

        try {
            $collection = $this->pimCategoryCollection();
            $dataArray = $collection->getData();
            $dataArray = $this->buildCategoryTree($dataArray);
            $this->executeTree($dataArray);
        } catch (\Exception $e) {
            echo self::WRONG_PIM_COLLECTION_MESSAGE . PHP_EOL;
            echo $e->getMessage() . "\n" . PHP_EOL;
        }
    }


    public function executeTree($collection)
    {
        foreach ($collection as $key => $data) {
            try {
                $mageId = $this->creatingCategory($data);
                if (array_key_exists('children', $data)) {
                    $this->executeTree($data['children']);
                }
            } catch (\Exception $e) {
                echo self::FAILED_MESSAGE . $data['Id'] . PHP_EOL;
                echo $e->getMessage() . "\n" . PHP_EOL;
            }
        }
        echo self::SUCCESS_MESSAGE . PHP_EOL;
    }

    public function creatingCategory($row)
    {   echo 'start'.$row['Id'].PHP_EOL;
        $this->startImport($row);
        $this->category = $this->categoryFactory->create();
        $this->setCategoryName($row);
        $this->setCategoryParentId($row);
        $this->setCategoryActiveStatus($row);
        $this->setCategoryCustomData($row);
        $this->setUpdateOrSave($row);
        try {
            $objCategory = $this->categoryRepositoryInterface->save($this->category);
            $objCategory = $this->updatePimData($objCategory, $row);
            $this->doneImport($row);


            echo 'End'.$row['Id'].PHP_EOL;
        } catch (\Exception $e) {
            $this->logger->info(self::SAVE_FAILED. $row['Id'] . PHP_EOL . '. ' . $e->getMessage());
            $this->showExceptionMessage($e, $row,self::SAVE_FAILED);
        }
    }


    public function getCategoriesExistsOrNot($category, $name)
    {
        return $category->getCollection()->addAttributeToFilter('name', $name)->getFirstItem();
    }

    public function getPimConnection()
    {

        return $this->resourceConnection->getConnection('pim');
    }

    public function pimCategoryCollection()
    {
        $collection = $this->pimCategoryFactory->create()->getCollection();

        $collection = $collection
            //->addFieldToFilter('magento_sync_status', ['null' => true])
            ->addFieldToFilter('ChannelId', ['eq' => '2'])
            ->addFieldToFilter('Active', ['eq' => '1']);
        $collection->setOrder('ParentId', 'ASC');

        return $collection;

    }

    public function buildCategoryTree(array &$elements, $parentId = 0)
    {

        $branch = array();
        foreach ($elements as $element) {
            if ($element['ParentId'] == $parentId) {
                $children = $this->buildCategoryTree($elements, $element['Id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['Id']] = $element;
            }
        }
        return $branch;
    }

    public function setCategoryCustomData($row)
    {
        if ($this->category && $row) {
            $this->category->setCustomAttributes([
                //'description' => '',
                'meta_title' => $row['Name'],
                'meta_keywords' => $row['Name'],
                'is_new' => '0',
                'is_sale' => '0',
                'meta_description' => '',
                'pim_category_id' => $row['Id'],
                'pim_category_active_status' => $row['Active'],
                'pim_category_channel_id' => $row['ChannelId'],
                'pim_category_code' => $row['Code'],
                'pim_category_external_id' => $row['ExternalId'],
                'pim_category_parent_id' => $row['ParentId']

            ]);
        }

    }

    public function updatePimData($objCategory, $row)
    {
        if ($objCategory && $row) {
            $collection = $this->pimCategoryFactory->create()->load($row['Id']);
            $collection->setData(self::PIM_MAGENTO_SYNC_STATUS, '1');
            $collection->setData(self::PIM_MAGENTO_CATEGORY_ID, $objCategory->getId());
            $collection->setData(self::PIM_MAGENTO_PARENT_CATEGORY_ID, $objCategory->getParentId());
            $collection->save();

        }
    }

    public function setCategoryName($row)
    {
        if ($this->category && $row) {
            $name = $row['Name'] ?? '';
            $this->category->setName($name);
        }
    }

    public function setCategoryParentId($row)
    {
        if ($this->category && $row) {
            $magentoCategoryId = $row['magento_category_id'] ?? '';
            $magentoParentCategoryId = $row['magento_parent_category_id'] ?? '';
            $parentId = $row['ParentId'] ?? '';

            if ($parentId && empty($magentoParentCategoryId) && empty($magentoCategoryId)) {
                $parentId = $this->categoryRepositoryInterface->getByPimParentId($parentId);
            } elseif (!empty($magentoParentCategoryId) && !empty($magentoCategoryId)) {
                $parentId = $magentoParentCategoryId;
            } else {
                $parentId = self::MAGENTO_ROOT_CATEGORY_ID;
            }

            $this->category->setParentId($parentId);
        }
    }

    public function setCategoryActiveStatus($row)
    {
        if ($this->category && $row) {
            $active = $row['Active'] ?? '0';
            $this->category->setIsActive($active);
        }
    }


    public function startImport($row)
    {
        $id = $row['Id'] ?? '';
        echo self::START_MESSAGE . $id . PHP_EOL;
    }

    public function doneImport($row)
    {

        echo 'Pim Category Id ' . $row['Id'] . ' Created/Updated =>>>>> ' . $row['Name'] . PHP_EOL;

    }

    public function setUpdateOrSave($row)
    {
        $magentoCategoryId = $row['magento_category_id'] ?? '';
        if ($magentoCategoryId) {
            $this->category->setId($magentoCategoryId);
        }
    }

    public function showExceptionMessage($e, $row=null,$customMessage = null)
    {
        if($row) {
            echo $customMessage . $row['Id'] . PHP_EOL;
        }
        echo $e->getMessage() . "\n" . PHP_EOL;
    }
}
