<?php

namespace Wcb\Checkout\Model\AdvancedCheckout;

use function array_map;
use function is_float;
use Magento\AdvancedCheckout\Helper\Data;
use Magento\AdvancedCheckout\Model\AreProductsSalableForRequestedQtyInterface;
use Magento\AdvancedCheckout\Model\Data\ProductQuantity;
use Magento\AdvancedCheckout\Model\IsProductInStockInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\CartConfiguration;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\WishlistFactory;

class Cart extends \Magento\AdvancedCheckout\Model\Cart
{
    protected $_eavAttribute;
    protected $resourceConnection;
    private $productCollectionFactory;
    private $areProductsSalableForRequestedQty;
    private $products = [];

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        Factory $messageFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Data $checkoutData,
        OptionFactory $optionFactory,
        WishlistFactory $wishlistFactory,
        CartRepositoryInterface $quoteRepository,
        StoreManagerInterface $storeManager,
        FormatInterface $localeFormat,
        ManagerInterface $messageManager,
        ConfigInterface $productTypeConfig,
        CartConfiguration $productConfiguration,
        Session $customerSession,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        Stock $stockHelper,
        ProductRepositoryInterface $productRepository,
        QuoteFactory $quoteFactory,
        Attribute $eavAttribute,
        ResourceConnection $resourceConnection,
        $itemFailedStatus = Data::ADD_ITEM_STATUS_FAILED_SKU,
        array $data = [],
        Json $serializer = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        IsProductInStockInterface $isProductInStock = null,
        AreProductsSalableForRequestedQtyInterface $areProductsSalableForRequestedQty = null,
        CollectionFactory $productCollectionFactory = null
    ) {
        $this->_eavAttribute = $eavAttribute;
        $this->resourceConnection = $resourceConnection;
        $this->areProductsSalableForRequestedQty = $areProductsSalableForRequestedQty
            ?? ObjectManager::getInstance()->get(AreProductsSalableForRequestedQtyInterface::class);
        $this->productCollectionFactory = $productCollectionFactory
            ?? ObjectManager::getInstance()->get(CollectionFactory::class);
        parent::__construct($cart, $messageFactory, $eventManager, $checkoutData, $optionFactory, $wishlistFactory, $quoteRepository, $storeManager, $localeFormat, $messageManager, $productTypeConfig, $productConfiguration, $customerSession, $stockRegistry, $stockState, $stockHelper, $productRepository, $quoteFactory, $itemFailedStatus, $data, $serializer, $searchCriteriaBuilder, $isProductInStock, $areProductsSalableForRequestedQty, $productCollectionFactory);
    }

    public function checkItems(array $items): array
    {
        $checkedItems = [];
        foreach ($items as $item) {
            if (empty($item['sku'])) {
                continue;
            }
            $sku = str_replace(" ", "", $item['sku']);
            $qty = $item['qty'] ?? '';
            $qty = is_float($qty) && isset($checkedItems[$sku]) ? ($qty + $checkedItems[$sku]['qty']) : $qty;
            $checkedItems[$sku] = $this->_getValidatedItem($sku, $qty);
        }

        $checkedItems = $this->areProductsSalable($checkedItems);
        $products = $this->preloadProducts($checkedItems);
        foreach ($checkedItems as $sku => &$item) {
            $itemProduct = $products[$sku] ?? null;
            $item = $this->checkItem(
                $sku,
                $item['qty'],
                [
                    '__item' => $item,
                    'product' => $itemProduct
                ]
            );
        }

        return $checkedItems;
    }

    private function areProductsSalable(array $items): array
    {
        if ($this->_isCheckout()) {
            $skuQuantities = array_map(function ($item) {
                return new ProductQuantity(
                    $item['sku'] = $item['sku'] ? $item['sku'] : '',
                    (float)($item['qty'] ?? 0)
                );
            }, $items);

            $itemsStockStatus = $this->areProductsSalableForRequestedQty->execute(
                $skuQuantities,
                (int)$this->getStore()->getWebsiteId()
            );
            foreach ($itemsStockStatus as $stockStatus) {
                if (!$stockStatus->isSalable()) {
                    //$item = &$items[$stockStatus->getSku()];
                    $item = &$items[$stockStatus->getSku()];
                    $item['is_configure_disabled'] = true;
                    $item['is_qty_disabled'] = true;
                    $item = $this->_updateItem($item, Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK);
                }
            }
        }
        return $items;
    }

    private function preloadProducts(array $items)
    {
        $skuForFind = array_diff(array_keys($items), array_keys($this->products));
        $products = [];
        if ($skuForFind) {
            //get without space product code and id
            $productIds = $this->getProductCodeWithProductId($skuForFind);
            /** @var Collection $collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            //$collection->addFieldToFilter('sku', ['in' => $skuForFind]);
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            //$collection->getSelect()->where("REPLACE('product_code',' ','')", ['in' => $skuForFind]);
            // $collection->setFlag('has_stock_status_filter', false);
            $itemsLowerCase = array_combine(array_map('mb_strtolower', array_keys($items)), $items);

            //remove space in product code (in array keys)
            $keys = str_replace(' ', '', array_keys($itemsLowerCase));
            $itemsLowerCase = array_combine($keys, array_values($itemsLowerCase));

            foreach ($collection as $product) {
                //$sku = $product->getSku();
                $sku = str_replace(' ', '', $product->getProductCode());
                $isSalable = true;
                if ($itemsLowerCase[mb_strtolower($sku)]['code'] === Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK) {
                    $isSalable = false;
                }

                $product->setIsSalable($isSalable);
                $products[$sku] = $product;
                $this->addProductToLocalCache($product, $product->getStoreId());
            }
        }

        return $products;
    }

    public function getProductCodeWithProductId($productsCode)
    {
        // remove space in user enter code and use
        $productCodeValue = [];
        foreach ($productsCode as $_productcode) {
            $productCodeValue[] = str_replace(' ', '', $_productcode);
        }
        if (empty($productCodeValue)) {
            return [];
        }
        $productCodeValue = implode(',', $productCodeValue);

        $productCodeId = $this->getProductCodeAttributeId();
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('catalog_product_entity_text');
        $query = "SELECT row_id FROM " . $table . " WHERE attribute_id = $productCodeId && REPLACE(value,' ','') IN ($productCodeValue)";
        $data = $connection->fetchAll($query);
        $productData = [];

        foreach ($data as $row) {
            if (isset($row['row_id'])) {
                $productData[] = $row['row_id'];
            }
        }
        return $productData;
    }

    public function getProductCodeAttributeId()
    {
        return $this->_eavAttribute->getIdByCode('catalog_product', 'product_code');
    }

    private function addProductToLocalCache(ProductInterface $product, int $storeId)
    {
        $this->products[str_replace(' ', '', $product->getProductCode())][$storeId] = $product;
    }

    protected function _loadProductBySku($sku)
    {
        $storeId = $this->getCurrentStore()->getId();
        $product = $this->getProductFromLocalCache($sku, $storeId);
        if (null === $product) {
            try {
                $product = $this->productRepository->get($sku, false, $storeId);
                $this->addProductToLocalCache($product, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return $product;
    }

    private function getProductFromLocalCache(string $sku, int $storeId)
    {
        if (!isset($this->products[$sku])) {
            return null;
        }

        return $this->products[$sku][$storeId] ?? null;
    }
}
