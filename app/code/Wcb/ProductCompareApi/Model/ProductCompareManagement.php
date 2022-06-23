<?php

namespace Wcb\ProductCompareApi\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\CustomerData\CompareProducts;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product\Compare;

use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Store\Model\StoreManagerInterface;
use Wcb\ProductCompareApi\Api\ProductCompareManagementInterface;

/**
 * Defines the implementaiton class of the \Wcb\ProductCompareApi\Api\ProductCompareManagementInterface
 */
class ProductCompareManagement implements ProductCompareManagementInterface
{

    /**
     * Product Compare Items Collection
     *
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Item
     */
    protected $_itemFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var AddToCompareAvailability|null
     */
    private $compareAvailability;
    /**
     * @var ObjectManagerInterface
     */
    private $_objectmanager;
    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;
    /**
     * @var ListCompare
     */
    private $_catalogProductCompareList;
    /**
     * @var ComparedFactory
     */
    private $compareFactory;
    /**
     * @var Item
     */
    private $compareItem;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CompareProducts
     */
    private $compareProducts;
    /**
     * @var Visibility
     */
    private $_catalogProductVisibility;
    /**
     * @var Url
     */
    private $productUrl;
    /**
     * @var Output
     */
    private $outputHelper;
    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        ObjectManagerInterface $_objectManager,
        AddToCompareAvailability $compareAvailability = null,
        ListCompare $catalogProductCompareList,
        ComparedFactory $compareFactory,
        Item $compareItem,
        CollectionFactory $collectionFactory,
        Compare $compareHelper,
        CompareProducts $compareProducts,
        Visibility $catalogProductVisibility,
        Url $productUrl,
        Output $outputHelper,
        CatalogConfig $catalogConfig
    ) {
        $this->_productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $_objectManager;
        $this->compareAvailability = $compareAvailability ?: $this->_objectManager->get(AddToCompareAvailability::class);
        $this->_catalogProductCompareList = $catalogProductCompareList;
        $this->compareFactory = $compareFactory;
        $this->compareItem = $compareItem;
        $this->collectionFactory = $collectionFactory;
        $this->compareHelper = $compareHelper;
        $this->compareProducts = $compareProducts;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->productUrl = $productUrl;
        $this->outputHelper = $outputHelper;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Get wishlist collection
     * @param int $customerId
     * @return array WishlistData
     */
    public function getProductCompareForCustomer($customerId)
    {
        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            $message = __('Id required');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        } else {
            $compareData = [];
            if (!$this->_itemCollection) {
                $this->compareHelper->setAllowUsedFlat(false);
                // cannot be placed in constructor because of the cyclic dependency which cannot be fixed with proxy class
                // collection uses this helper in constructor when calling isEnabledFlat() method
                $this->_itemCollection = $this->collectionFactory->create();
                $this->_itemCollection->useProductItem()->setStoreId($this->_storeManager->getStore()->getId());

                $this->_itemCollection->setCustomerId($customerId);
               // $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());
                /* Price data is added to consider item stock status using price index */
                //$this->_itemCollection->addPriceData();

                $this->_itemCollection->addAttributeToSelect(
                    $this->catalogConfig->getProductAttributes()
                )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()->setVisibility(
                    $this->_catalogProductVisibility->getVisibleInSiteIds()
                );
                /* update compare items count */
                //$this->_catalogSession->setCatalogCompareItemsCount(count($this->_itemCollection));
            }

            foreach ($this->_itemCollection as $item) {
                $items[] = $item->getData();
            }
            $compareData[] = [
                'count' => count($this->_itemCollection),
                'items' => $items,
                'status' => true
            ];

            return $compareData;
        }
    }

    /**
     * @param int $customerId
     * @param int $productId
     * @return array|bool
     */
    public function addProductCompareForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            $message = __('Invalid product, Please select a valid product');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $product = $this->_productRepository->getById($productId, false, $storeId);
        } catch (Exception $e) {
            $product = null;
            return false;
        }
        try {
            if ($product && $this->compareAvailability->isAvailableForCompare($product)) {
                $this->compareItem->setCustomerId($customerId);
                $this->compareItem->addProductData($productId);
                $this->compareItem->save();
                $this->compareItem->unsetData();

                $viewData = [
                    'product_id' => $productId,
                    'customer_id' => $customerId
                ];
                $this->compareFactory->create()->setData($viewData)->save();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        $message = __('Item added to compare.');
        $status = true;
        $response[] = [
            "message" => $message,
            "status" => $status
        ];
        return $response;
    }

    /**
     * @param int $customerId
     * @return array|string
     */
    public function clearProductCompareForCustomer($customerId)
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->setCustomerId($customerId);
            $collection->clear();
            $this->compareHelper->calculate();
            return "success";
        } catch (Exception $e) {
            return flase;
        }
    }

    /**
     * @param int $customerId
     * @param int $compareItemId
     * @return array|bool
     */
    public function deleteProductCompareForCustomer($customerId, $compareItemId)
    {
        $message = null;
        $status = null;
        if ($compareItemId == null || $compareItemId == '') {
            $message = __('Invalid Compare product item, Please select a valid item');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }

        $compare = $this->compareItem->load($compareItemId);
        if (!$compare->getCatalogCompareItemId() && $compare->getCustomerId != $customerId) {
            $message = __('The requested Compare List Item doesn\'t exist .');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        try {
            $compare->delete();
        } catch (Exception $e) {
            return false;
        }
        $message = __(' Item has been removed from wishlist .');
        $status = true;
        $response[] = [
            "message" => $message,
            "status" => $status
        ];
        return $response;
    }
}
