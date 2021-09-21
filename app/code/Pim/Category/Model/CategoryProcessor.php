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
    const MAGENTO_ROOT_CATEGORY_ID = '2';

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
        \Magento\Catalog\Model\CategoryRepository        $categoryRepository


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
    }


    public function initExecution()
    {

        try {
            $collection = $this->pimCategoryCollection();
            $dataArray = $collection->getData();
            $dataArray = $this->buildCategoryTree($dataArray);
            $this->executeTree($dataArray);
        } catch (\Exception $e) {
            echo 'Something went wrong in pim collection  ' . PHP_EOL;
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
                echo 'Failed to create category Pim Id  ' . $data['Id'] . PHP_EOL;
                echo $e->getMessage() . "\n" . PHP_EOL;
            }
        }
        echo 'Category Sync/Build Has been done' . PHP_EOL;
    }

    public function creatingCategory($row)
    {
        $name = $row['Name'] ?? '';
        $active = $row['Active'] ?? '0';
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
        echo  'Done For Pim Category Id  ->>' . $parentId. PHP_EOL;
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
            $collection = $this->pimCategoryFactory->create()->load($row['Id']);
            $collection->setData(self::PIM_MAGENTO_SYNC_STATUS, '1');
            $collection->setData(self::PIM_MAGENTO_CATEGORY_ID, $objCategory->getId());
            $collection->setData(self::PIM_MAGENTO_PARENT_CATEGORY_ID, $objCategory->getParentId());
            $collection->save();

        }
        echo 'Pim Category Id ' . $row['Id'] . ' Created/Updated =>>>>> ' . $name . PHP_EOL;
        return $objCategory->getId();
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
}
