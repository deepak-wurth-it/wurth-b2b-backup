<?php
namespace Wcb\ApiConnect\Model;

use Wcb\ApiConnect\Api\WishlistManagementInterface;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Helper\ImageFactory as ProductImageHelper;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Defines the implementaiton class of the WishlistManagementInterface
 */
class WishlistManagement implements WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
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
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     *@var \Magento\Catalog\Helper\ImageFactory
     */
    protected $productImageHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storemanagerinterface;

    /**
     *@var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     *@var \Magento\Catalog\Model\Product
     */
    protected $_productload;

    /**
     *@var \Magento\Directory\Model\CountryFactory
     */
    protected $countryfactory;

    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\Customer $customer,
        AppEmulation $appEmulation,
        \Magento\Directory\Model\CountryFactory $countryfactory,
        \Magento\Store\Model\StoreManagerInterface $storemanagerinterface,
        ProductImageHelper $productImageHelper,
        \Magento\Catalog\Model\Product $productload,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_wishlistRepository = $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->countryfactory = $countryfactory;
        $this->storemanagerinterface = $storemanagerinterface;
        $this->_itemFactory = $itemFactory;
        $this->_customer = $customer;
        $this->_productload = $productload;
        $this->appEmulation = $appEmulation;
        $this->productImageHelper = $productImageHelper;
        $this->_customer = $customer;
    }

    /**
     * Get wishlist collection
     * @deprecated
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {

        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Id required'));
        } else {
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);
            $baseurl = $this->storemanagerinterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
            $wishlistData = [];
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();

                //get final prices
                $prices = '';//$this->getPriceRange($product, $groupId);

                //set min price
                if (isset($productInfo['min_price']) && isset($prices['min'])) {
                    $productInfo['minimal_price'] = $prices['min'];
                    $productInfo['min_price'] = $prices['min'];
                }

                //set max price
                if (isset($productInfo['max_price']) && isset($prices['max'])) {
                    $productInfo['max_price'] = $prices['max'];
                }

                if ($productInfo['small_image'] == 'no_selection') {
                    $currentproduct = $this->_productload->load($productInfo['entity_id']);
                    $imageURL = $this->getImageUrl($currentproduct, 'product_base_image');
                    $productInfo['small_image'] = $imageURL;
                    $productInfo['thumbnail'] = $imageURL;
                }else{
                    $imageURL = $baseurl.$productInfo['small_image'];
                    $productInfo['small_image'] = $imageURL;
                    $productInfo['thumbnail'] = $imageURL;
                }
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
            return $wishlistData;
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $wishlist->addNewItem($product);
            $returnData = $wishlist->save();
        } catch (NoSuchEntityException $e) {

        }
        return true;
    }

    /**
     * Helper function that provides full cache image url
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    public function getImageUrl($product, $imageType = ''){
        $storeId = $this->storemanagerinterface->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageUrl = $this->productImageHelper->create()->init($product, $imageType)->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }


    /**
     * @param $product
     * @return array
     */
    public function getPriceRange($product, $groupId)
    {
        $childProductPrice = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);
        foreach ($childProducts as $child) {
            $qty = $this->getStockQty($child->getId());
            if ($child->isSaleable() && $qty>0) {
                $price = $child->getPrice();
                $finalPrice = $child->getFinalPrice();

                $child->setFinalPrice($finalPrice);
                $child->setCustomerGroupId($groupId);

                $this->eventManager->dispatch('catalog_product_get_final_price', ['product' => $child, 'qty' => $qty]);
                $finalPrice = $child->getData(self::FINAL_PRICE);

                if ($price == $finalPrice) {
                    $childProductPrice[] = $price;
                } else if ($finalPrice < $price) {
                    $childProductPrice[] = $finalPrice;
                }
            }
        }

        $min = min($childProductPrice);
        $max = max($childProductPrice);

        return [
            self::MIN => $min,
            self::MAX => $max
        ];
    }

    /**
     * @param $productId
     * @param null $websiteId
     * @return float
     */
    public function getStockQty($productId, $websiteId = null)
    {
        return $this->stockState->getStockQty($productId, $websiteId);
    }
}
