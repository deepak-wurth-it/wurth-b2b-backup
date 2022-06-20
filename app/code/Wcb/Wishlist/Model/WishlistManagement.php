<?php

namespace Wcb\Wishlist\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishListCollection;
use Magento\Wishlist\Model\WishlistFactory;
use Wcb\Wishlist\Api\WishlistManagementInterface;

/**
 * Defines the implementaiton class of the \Wcb\Wishlist\Api\WishlistManagementInterface
 */
class WishlistManagement implements
    WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;

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
     * @var WishListCollection
     */
    private $wishListCollection;

    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param ProductFactory $productFactory
     * @param Random $mathRandom
     * @param DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        ProductRepositoryInterface $productRepository,
        ItemFactory $itemFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        WishListCollection $wishListCollection
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->wishListCollection = $wishListCollection;
    }

    /**
     * Get wishlist collection
     * @param int $customerId
     * @return array WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {
        $customerIds = [];
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getCustomAttribute('customer_code')) {
            $customerCode = $customer->getCustomAttribute('customer_code')->getValue();
            $sameCustomerCodeCollection = $this->getCustomerByCustomerCode($customerCode);
            foreach ($sameCustomerCodeCollection as $_customer) {
                $customerIds[] = $_customer->getId();
            }
        }
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

            /* $this->_wishlistCollectionFactory->create()
            ->addCustomerIdFilter($customerId);*/

            /*Remove filter from customer id as per our requirement */
            $wishlistCollection = $this->wishListCollection->create()
                ->addFieldToFilter('customer_id', ['in' => $customerIds]);
            $list = $wishlistCollection->getColumnValues('wishlist_id');
            if (empty($list)) {
                return $wishlistData;
            }
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addFieldToFilter('wishlist_id', ['in' => $list]);

            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id" => $item->getWishlistId(),
                    "product_id" => $item->getProductId(),
                    "store_id" => $item->getStoreId(),
                    "added_at" => $item->getAddedAt(),
                    "description" => $item->getDescription(),
                    "qty" => round($item->getQty()),
                    "product" => $productInfo
                ];
                $wishlistData[] = $data;
            }
            return $wishlistData;
        }
    }

    public function getCustomerByCustomerCode($customerCode)
    {
        return $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("customer_code", ["eq" => $customerCode]);
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     *
     */
    public function addWishlistForCustomer($customerId, $productId)
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
            $product = $this->_productRepository->getById($productId);
        } catch (Exception $e) {
            return false;
        }
        try {
            $wishlist = $this->_wishlistFactory->create()
                ->loadByCustomerId($customerId, true);
            //$wishlist->addNewItem($product);
            $item = $wishlist->addNewItem($product);
            $postData = file_get_contents("php://input");
            if ($postData) {
                $postData = json_decode($postData, true);
            }
            if (isset($postData['description'])) {
                $item->setDescription($postData['description']);
            }
            $wishlist->save();
        } catch (Exception $e) {
            return false;
        }
        $message = __('Item added to wishlist.');
        $status = true;
        $response[] = [
            "message" => $message,
            "status" => $status
        ];
        return $response;
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return array
     *
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {
        $message = null;
        $status = null;
        if ($wishlistItemId == null) {
            $message = __('Invalid wishlist item, Please select a valid item');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;

            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        //if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
        /**
         * Remove customer condition to delete wishlist item if the customer is valid not only who have added to the product in the wishlist.
         */
        if (!$wishlist->getId()) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;
            $response[] = [
                "message" => $message,
                "status" => $status
            ];
            return $response;
        }
        try {
            $item->delete();
            $wishlist->save();
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
