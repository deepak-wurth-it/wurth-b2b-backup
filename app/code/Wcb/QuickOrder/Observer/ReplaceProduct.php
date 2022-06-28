<?php

namespace Wcb\QuickOrder\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as productCollection;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Wcb\Checkout\Helper\ManageProductStatus;

class ReplaceProduct implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var SessionFactory
     */
    protected $checkoutSession;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;
    /**
     * @var productCollection
     */
    protected $productCollectionFactory;

    /**
     * ReplaceProduct constructor.
     * @param ProductRepositoryInterface $productrepositoryInterface
     * @param JsonSerializer $jsonSerializer
     * @param productCollection $productCollectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productrepositoryInterface,
        JsonSerializer $jsonSerializer,
        productCollection $productCollectionFactory
    ) {
        $this->productRepository = $productrepositoryInterface;
        $this->jsonSerializer = $jsonSerializer;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getRequest()->getParam('items');
        $errorType = $observer->getRequest()->getParam('errorType');
        $newItems = [];
        if ($items) {
            $items = $this->jsonSerializer->unserialize($items);
            if (count($items) && $errorType === 'file') {
                //get product codes
                $productCodes = array_map(function ($x) {
                    return $x['sku'];
                }, $items);
                //check product code has successor products
                $successorCodes = $this->getSuccessorCodeByProductCodes($productCodes);

                // replace successor products
                foreach ($items as $_item) {
                    if (isset($_item['sku'])) {
                        if (isset($successorCodes[$_item['sku']])) {
                            $_item['sku'] = $successorCodes[$_item['sku']];
                        }
                    }
                    $newItems[] = $_item;
                }
                if (!empty($newItems)) {
                    $newItems = $this->jsonSerializer->serialize($newItems);
                    $observer->getRequest()->setParam('items', $newItems);
                    $observer->getRequest()->setPostValue('items', $newItems);
                }
            }
        }
    }

    public function getSuccessorCodeByProductCodes($productCodes)
    {
        $products = $this->productCollectionFactory->create()
            ->addAttributeToFilter("product_code", ["in" => [$productCodes]]);
        $replaceProductsData = [];

        foreach ($products as $product) {
            $product = $this->productRepository->getById($product->getId());
            $wcbProductStatus = $product->getWcbProductStatus();
            $replaceProductCode = $product->getSuccessorProductCode();

            if (($wcbProductStatus == '3' || $wcbProductStatus == '2') && $replaceProductCode) {
                if ($replaceProductCode) {
                    $replaceProductsData[$product->getProductCode()] = $replaceProductCode;
                }
            }
        }
        return $replaceProductsData;
    }
}
