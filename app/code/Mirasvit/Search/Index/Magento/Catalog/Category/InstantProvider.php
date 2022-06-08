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

declare(strict_types=1);

namespace Mirasvit\Search\Index\Magento\Catalog\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends AbstractInstantProvider
{
    private $categoryRepository;

    private $storeManager;

    protected $urlBuilder;

    public function __construct(
        Url $urlBuilder,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        IndexService $indexService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager       = $storeManager;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($indexService);
    }

    public function getItems(int $storeId, int $limit): array
    {
        $items = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($this->getCollection($limit) as $category) {
            $items[] = $this->mapCategory($category, $storeId);
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int                             $storeId
     *
     * @return array
     */
    private function mapCategory($category, int $storeId): array
    {
        $category = $category->setStoreId($storeId);
        $category = $category->load($category->getId());
        if ($category->getUrl()) {
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $category->getUrl();
        } else {
            $imageUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . "catalog/product/placeholder/default/replacement_product_2.png";
        }
        return [
            'name' => $this->getFullPath($category, $storeId),
            'url'  => $imageUrl,
            'image'  => $category->getImageUrl(),
        ];
    }

    private function getFullPath(CategoryInterface $category, int $storeId): string
    {
        $store  = $this->storeManager->getStore($storeId);
        $rootId = $store->getRootCategoryId();

        $result = [
            $category->getName(),
        ];

        do {
            break; // skip breadcrumbs

            if (!$category->getParentId()) {
                break;
            }
            $category = $this->categoryRepository->get($category->getParentId());
            $category = $category->setStoreId($storeId);
            $category->load($category->getId());

            if (!$category->getIsActive() && $category->getId() != $rootId) {
                break;
            }

            if ($category->getId() != $rootId) {
                $result[] = $category->getName();
            }
        } while ($category->getId() != $rootId);

        $result = array_reverse($result);

        return implode('<i>›</i>', $result);
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        foreach ($documentData as $entityId => $itm) {
            $entity = ObjectManager::getInstance()->create('\Magento\Catalog\Model\Category')
                ->load($entityId);

            $map = $this->mapCategory($entity, $storeId);

            $documentData[$entityId]['_instant'] = $map;
        }

        return $documentData;
    }
}
