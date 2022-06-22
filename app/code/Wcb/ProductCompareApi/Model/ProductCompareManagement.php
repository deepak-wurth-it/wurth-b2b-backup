<?php

namespace Wcb\ProductCompareApi\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;

use Wcb\ProductCompareApi\Api\ProductCompareManagementInterface;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Model\Product\Compare\ListCompare;

use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Helper\Product\Compare;
/**
 * Defines the implementaiton class of the \Wcb\ProductCompareApi\Api\ProductCompareManagementInterface
 */
class ProductCompareManagement implements ProductCompareManagementInterface
{

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
     * @var \Magento\Catalog\CustomerData\CompareProducts
     */
    private $compareProducts;


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
        Compare $compareHelper
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
    }

    /**
     * Get wishlist collection
     * @param int $customerId
     * @return array WishlistData
     */
    public function getProductCompareForCustomer($customerId)
    {
        $customerIds = [];
        return json_encode($this->compareProducts->getSectionData());
        exit;
         if (empty($customerId) || !isset($customerId) || $customerId == "") {
             $message = __('Id required');
             $status = false;
             $response[] = [
                 "message" => $message,
                 "status" => $status
             ];
             return $response;
         } else {
             $wishlistData = [];
             return $wishlistData;
         }
    }


    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     *
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
     * @return array|void
     */
    public function clearProductCompareForCustomer($customerId)
    {
        $collection = $this->collectionFactory->create();
        $collection->setCustomerId($customerId);
        $collection->clear();
        $this->compareHelper->calculate();
        return "success";
    }


    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return array
     *
     */
    public function deleteProductCompareForCustomer($customerId, $compareListId)
    {
        $message = null;
        $status = null;
        if ($compareListId == null) {

        }



//        if ($wishlistItemId == null) {
//            $message = __('Invalid wishlist item, Please select a valid item');
//            $status = false;
//            $response[] = [
//                "message" => $message,
//                "status" => $status
//            ];
//            return $response;
//        }
//        $item = $this->_itemFactory->create()->load($wishlistItemId);
//        if (!$item->getId()) {
//            $message = __('The requested Wish List Item doesn\'t exist .');
//            $status = false;
//
//            $response[] = [
//                "message" => $message,
//                "status" => $status
//            ];
//            return $response;
//        }
//        $wishlistId = $item->getWishlistId();
//        $wishlist = $this->_wishlistFactory->create();
//
//        if ($wishlistId) {
//            $wishlist->load($wishlistId);
//        } elseif ($customerId) {
//            $wishlist->loadByCustomerId($customerId, true);
//        }
//        if (!$wishlist) {
//            $message = __('The requested Wish List Item doesn\'t exist .');
//            $status = false;
//            $response[] = [
//                "message" => $message,
//                "status" => $status
//            ];
//            return $response;
//        }
//        //if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
//        /**
//         * Remove customer condition to delete wishlist item if the customer is valid not only who have added to the product in the wishlist.
//         */
//        if (!$wishlist->getId()) {
//            $message = __('The requested Wish List Item doesn\'t exist .');
//            $status = false;
//            $response[] = [
//                "message" => $message,
//                "status" => $status
//            ];
//            return $response;
//        }
//        try {
//            $item->delete();
//            $wishlist->save();
//        } catch (Exception $e) {
//            return false;
//        }

        $message = __(' Item has been removed from wishlist .');
        $status = true;
        $response[] = [
            "message" => $message,
            "status" => $status
        ];
        return $response;
    }
}
