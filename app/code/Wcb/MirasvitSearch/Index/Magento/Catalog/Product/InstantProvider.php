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

namespace Wcb\MirasvitSearch\Index\Magento\Catalog\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Magento\Review\Model\Review\Summary;
use Mirasvit\Search\Index\Magento\Catalog\Product\InstantProvider\Mapper;
use Mirasvit\Search\Service\IndexService;

class InstantProvider extends \Mirasvit\Search\Index\Magento\Catalog\Product\InstantProvider
{
    protected $productRepository;
    private $mapper;
    private $summaryFactory;
    private $productCollectionFactory;
    private $reviews = [];

    public function __construct(
        Mapper $mapper,
        SummaryCollectionFactory $summaryFactory,
        ProductCollectionFactory $productCollectionFactory,
        IndexService $indexService,
        ProductRepositoryInterface $productrepositoryInterface
    ) {
        $this->mapper = $mapper;
        $this->summaryFactory = $summaryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productrepositoryInterface;
        parent::__construct($mapper, $summaryFactory, $productCollectionFactory, $indexService);
    }

    public function getItems(int $storeId, int $limit): array
    {
        /** @var Collection $collection */
        $collection = $this->getCollection($limit)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('description')
            ->setOrder('relevance');

        $this->prepareRatingData($collection->getAllIds(), $storeId);

        $items = [];

        foreach ($collection as $product) {
            $items[] = $this->mapProduct($product, $storeId);
        }

        return $items;
    }

    private function prepareRatingData(array $productIds, int $storeId): void
    {
        $reviewsCollection = $this->summaryFactory->create()
            ->addEntityFilter($productIds)
            ->addStoreFilter($storeId)
            ->load();

        /** @var Summary $reviewSummary */
        foreach ($reviewsCollection as $reviewSummary) {
            $this->reviews[$reviewSummary->getData('entity_pk_value')] = $reviewSummary;
        }
    }

    private function mapProduct(ProductInterface $product, int $storeId): array
    {
        $replaceProductData = $this->getReplaceProductData($product->getId());
        $replaceProductCode = isset($replaceProductData['replace_product_code']) ? $replaceProductData['replace_product_code'] : '';
        $replaceProductMsg = isset($replaceProductData['msg']) ? $replaceProductData['msg'] : '';
        $replaceProductUrl = isset($replaceProductData['url']) ? $replaceProductData['url'] : '';

        return [
            'name' => $this->mapper->getProductName($product),
            'url' => $this->mapper->getProductUrl($product, $storeId),
            'sku' => $this->mapper->getProductSku($product),
            'description' => $this->mapper->getDescription($product),
            'image' => $this->mapper->getProductImage($product, $storeId),
            'price' => $this->mapper->getPrice($product, $storeId),
            'rating' => $this->mapper->getRating($product, $storeId, $this->reviews),
            'cart' => $this->mapper->getCart($product, $storeId),
            'stock_status' => $this->mapper->getStockStatus($product, $storeId),
            'replace_product_code' => $replaceProductCode,
            'replace_product_msg' => $replaceProductMsg,
            'replace_product_url' => $replaceProductUrl
        ];
    }

    public function getReplaceProductData($productId)
    {
        $product = $this->productRepository->getById($productId);
        $wcbProductStatus = $product->getWcbProductStatus();
        $replaceProductCode = $product->getSuccessorProductCode();
        $returnData = [];
        $returnData['msg'] = 'test-';
        $returnData['url'] = '';
        $returnData['replace_product_code'] = '';
        if ($wcbProductStatus == 3 || $wcbProductStatus == 2) {
            if ($replaceProductCode) {
                $returnMsg = __("This is replacement product for this " . $replaceProductCode);
                $returnData['replace_product_code'] = $replaceProductCode;
                $replaceProduct = $this->getProductByProductCode($replaceProductCode);
                if ($replaceProduct->getId()) {
                    $returnData['url'] = $replaceProduct->getProductUrl();
                }
            } else {
                $returnMsg = __("You are not allowed to add this product.");
            }
            $returnData['msg'] ="test--" . $returnMsg;
        }
        return $returnData;
    }

    public function getProductByProductCode($productCode)
    {
        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('product_code', ['eq' => $productCode])
            ->getFirstItem();
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        $productIds = array_keys($documentData);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addStoreFilter($storeId);

        $this->prepareRatingData($productIds, $storeId);

        foreach ($productCollection as $product) {
            $documentData[$product->getId()]['_instant'] = $this->mapProduct($product, $storeId);
        }

        unset($productCollection);

        return $documentData;
    }
}
