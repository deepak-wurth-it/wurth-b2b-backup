<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Category\Model;

use Magento\Framework\ObjectManagerInterface;

class CategoryProcessor
{
    const PIM_CATEGORIES_TABLE = 'categories';
    const PIM_MAGENTO_SYNC_STATUS = 'magento_sync_status';
    const PIM_MAGENTO_CATEGORY_ID = 'magento_category_id';
    const PIM_MAGENTO_PARENT_CATEGORY_ID = 'magento_parent_category_id';

    /**
     * 
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->categoryFactory = $categoryFactory;
        $this->resourceConnection = $resourceConnection;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->objectManager = $objectManager;
        $this->fileCsv = $fileCsv;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
    }


    public function initExecution()
    {

        try {
            $collection = $this->pimCategoryCollection();
            //zecho "<pre>";print_r(get_class_methods($collection));exit;
            foreach ($collection as $data) {
                try {
                    $this->creatingCategory($data);
                } catch (\Exception $e) {
                    echo 'Failed to create category Pim Id  ' . $data['Id'] . PHP_EOL;
                    echo $e->getMessage() . "\n" . PHP_EOL;
                }
            }
            echo 'Category Sync/Build Has been done' . PHP_EOL;
        } catch (\Exception $e) {
            echo 'Something went wrong in pim collection  '. PHP_EOL;
            echo $e->getMessage() . "\n" . PHP_EOL;
        }
    }

    public function creatingCategory($row)
    {


        $name = $row['Name'] ? $row['Name'] : '';
        $active = $row['Active'] ? $row['Active'] : '0';
        $magentoCategoryId = $row['magento_category_id'];
        $magentoParentCategoryId = $row['magento_parent_category_id'];
        $parentId = $row['ParentId'];

        if ($parentId && empty($magentoParentCategoryId) && empty($magentoCategoryId)) {
            $parentId = $this->categoryRepositoryInterface->getByPimParentId($parentId);
        } elseif (!empty($magentoParentCategoryId) && !empty($magentoCategoryId)) {
            $parentId =  $magentoParentCategoryId;
        } else {
            $parentId =  2;
        }

        $category = $this->categoryFactory->create();

        $category->setName($name);
        $category->setParentId($parentId);
        $category->setIsActive($active);
        $category->setCustomAttributes([
            'description' => 'category example',
            'meta_title' => 'category example',
            'meta_keywords' => '',
            'meta_description' => '',
            'pim_category_id' => $row['Id'],
            'pim_category_active_status' => $row['Active'],
            'pim_category_channel_id' => $row['ChannelId'],
            'pim_category_code' => $row['Code'],
            'pim_category_external_id' => $row['ExternalId'],
            'pim_category_parent_id' => $row['ParentId']

        ]);

        if ($magentoCategoryId) {
            
            $category->setId($magentoCategoryId);
        }

        $objCategory = $this->categoryRepositoryInterface->save($category);

        if ($objCategory) {

            $connectionObject = $this->getPimConnection();
            $tableName = $connectionObject->getTableName(self::PIM_CATEGORIES_TABLE);
            $where = ['Id = ?' => $row['Id']];
            $data = [
                self::PIM_MAGENTO_SYNC_STATUS => '1',
                self::PIM_MAGENTO_CATEGORY_ID => $objCategory->getId(),
                self::PIM_MAGENTO_PARENT_CATEGORY_ID => $objCategory->getParentId()
            ];

            $connectionObject->update($tableName, $data, $where);
        }
        echo  'Pim Category Id '.$row['Id'].' Created/Updated =>>>>> ' . $name . PHP_EOL;
    }

    public function getCategoriesExistsOrNot($category, $name)
    {
        return $category->getCollection()->addAttributeToFilter('name', $name)->getFirstItem();
    }

    public function deleteAllCategories()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');
        $newCategory = $categoryFactory->create();
        $collection = $newCategory->getCollection();
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);

        foreach ($collection as $category) {

            $category_id = $category->getId();

            if ($category_id <= 2) continue;

            try {
                $category->delete();
                echo 'Category Removed ' . $category_id . PHP_EOL;
            } catch (Exception $e) {
                echo 'Failed to remove category ' . $category_id . PHP_EOL;
                echo $e->getMessage() . "\n" . PHP_EOL;
            }
        }
    }

    public function getPimConnection()
    {


        return $this->resourceConnection->getConnection('pim');
    }

    public function pimCategoryCollection()
    {

        $connection = $this->getPimConnection();
        $tableName = $connection->getTableName('categories');
        $query = $connection->select()->from($tableName, ['*'])
            ->where('Active =?', 1)
            ->where('magento_sync_status =?', 0)
            ->where('ChannelId =?', 2);
        $fetchData = $connection->fetchAll($query);
        return  $fetchData;
    }
}
