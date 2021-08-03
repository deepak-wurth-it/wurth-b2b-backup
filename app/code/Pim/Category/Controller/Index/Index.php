<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Pim\Category\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\CategoryFactory $catFactory,
        \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface
        // \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceConnection = $resourceConnection;
        $this->storeManagerInterface = $StoreManagerInterface;

        $this->catFactory = $catFactory;
        // $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->pim = $this->resourceConnection->getConnection('pim');

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        echo $this->getRootCategoryId();
        exit;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category'); //get current category
        echo $category->getParentCategory()->getName();

        exit;


        $connection = $this->pim;

        $tableName = $connection->getTableName('categories');
        $query = $connection->select()->from($tableName, ['*']);
        $objCat = $this->catFactory->create();

        $fetchData = $connection->fetchAll($query);
        $newCatArray = [];
        $i = 1;
        foreach ($fetchData as $row) {
            //echo $row['Name'];
            // $objCat->setName($row['Name']);
            //echo  $parentId = empty($row['ParentId']) ? '2' : $row['ParentId'];
            //$objCat->setParentId($parentId ); // 1: root category.
            // $objCat->setIsActive(true);
            // $this->categoryRepositoryInterface->save($objCat);
            // $category->setCustomAttributes([
            //         'description' => 'Computer 3 desc',
            // ]);
            // $newCatArray['id'] =  $row['Id'];
            // $newCatArray['name'] =  $row['Name'];
            // $newCatArray['parent_id'] =  $row['ParentId'];
            // $newCatArray['is_anchor'] =  '1';
            // $newCatArray['include_in_menu'] =  '1';
            // $newCatArray['pim_category_active_status'] =  '1';
            // $newCatArray['is_active'] =  $row['Active'];
            $i++;

            if($i==10){
                break;
            }

        }

        die('ok');
    }

    function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['ParentId'] == $parentId) {
                $children = $this->buildTree($elements, $element['Id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function createCategory()
    {
        global $objectManager;

        $category = $objectManager->get('\Magento\Catalog\Model\CategoryFactory')->create();

        $category->setName('Computer 3');
        $category->setParentId(1); // 1: root category.
        $category->setIsActive(true);
        $category->setCustomAttributes([
            'description' => 'Computer 3 desc',
        ]);

        $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface')->save($category);
    }
    public function getRootCategoryId()
    {
        return $this->storeManagerInterface->getStore()->getRootCategoryId();
    }

    // /**
    //  * @param array $row
    //  * @return void
    //  */
    // protected function createCategory($row)
    // {
    //     $category = $this->getCategoryByPath($row['path'] . '/' . $row['name']);
    //     if (!$category) {
    //         $parentCategory = $this->getCategoryByPath($row['path']);
    //         $data = [
    //             'parent_id' => $parentCategory->getId(),
    //             'name' => $row['name'],
    //             'is_active' => $row['active'],
    //             'is_anchor' => $row['is_anchor'],
    //             'include_in_menu' => $row['include_in_menu'],
    //             'url_key' => $row['url_key'],
    //         ];
    //         $category = $this->categoryFactory->create();
    //         $category->setData($data)
    //             ->setPath($parentCategory->getData('path'))
    //             ->setAttributeSetId($category->getDefaultAttributeSetId());
    //         $this->setAdditionalData($row, $category);
    //         $category->save();
    //     }
    // }
}
