<?php

namespace Wcb\RequisitionList\Controller\Items;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\CollectionFactory as RequisitionListCollection;
use Wcb\Checkout\Helper\Data as CheckoutHelper;
use Wcb\Checkout\Helper\MultiPriceAndStock;

class AddtocartList extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var RequisitionListCollection
     */
    protected $requisitionListCollection;
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var FormKey
     */
    protected $formKey;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;
    /**
     * @var ProductCollection
     */
    protected $productCollectionFactory;
    /**
     * @var MultiPriceAndStock
     */
    protected $multiPriceAndStock;

    /**
     * AddtocartList constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param RequisitionListCollection $requisitionListCollection
     * @param Cart $cart
     * @param FormKey $formkey
     * @param ProductRepositoryInterface $productRepository
     * @param CheckoutHelper $checkoutHelper
     * @param ProductCollection $productCollectionFactory
     * @param MultiPriceAndStock $multiPriceAndStock
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        RequisitionListCollection $requisitionListCollection,
        Cart $cart,
        FormKey $formkey,
        ProductRepositoryInterface $productRepository,
        CheckoutHelper $checkoutHelper,
        ProductCollection $productCollectionFactory,
        MultiPriceAndStock $multiPriceAndStock
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->requisitionListCollection = $requisitionListCollection;
        $this->cart = $cart;
        $this->formKey = $formkey;
        $this->productRepository = $productRepository;
        $this->checkoutHelper = $checkoutHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->multiPriceAndStock = $multiPriceAndStock;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $result = [];

        try {
            $listId = $this->getRequest()->getParam('list_id');
            $listData = $this->requisitionListCollection->create()
                ->addFieldToFilter("requisition_list_id", ["eq" => $listId]);
            if ($listData->count() > 0) {
                foreach ($listData as $listItem) {
                    $product = $this->productRepository->get($listItem->getSku());
                    $wcbProductStatus = $product->getWcbProductStatus();
                    $replaceProductCode = $product->getSuccessorProductCode();
                    $qty = (int)$listItem->getQty();

                    // for status 2
                    if ($wcbProductStatus == '2') {
                        $updateQty = $this->checkProductWithStockAPi($product, $qty);
                        if ($updateQty != '') {
                            $qty = $updateQty;
                        }
                    }

                    // for status 3
                    if ($wcbProductStatus == '3' && $replaceProductCode) {
                        $product = $this->getProductByProductCode($replaceProductCode);
                        //load again because custom attribute no get in collection
                        $product = $this->productRepository->get($product->getSku());
                        if (!$product->getId()) {
                            continue;
                        }
                    }
                    // check product enable or disabled
                    if ($product->getStatus() == '1') {
                        $unitQty = $this->checkoutHelper->getQuantityUnitByQuantity($qty, $product);

                        $params = [
                            'form_key' => $this->formKey->getFormKey(),
                            'product' => $product->getId(),
                            'qty' => $unitQty
                        ];
                        $this->cart->addProduct($product, $params);
                    }
                }
                $this->cart->save();

                $result['success'] = "true";
                $result['message'] = __("Order template items add to cart successfully.");
                $this->messageManager->addSuccess($result['message']);
            } else {
                $result['success'] = "false";
                $result['message'] = __("List not found in list.");
                $this->messageManager->addError($result['message']);
            }
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['message'] = __("Something went wrong please try again.");
            $this->messageManager->addError($result['message']);
        }
        $response->setData($result);
        return $response;
    }

    /**
     * @param $product
     * @param $qty
     * @return float|int|string
     */
    public function checkProductWithStockAPi($product, $qty)
    {
        // get stock using API
        $stockSku = [];
        $newQty = '';
        $stockSku['skus'][] = [
            "product_code" => $product->getProductCode(),
            "qty" => 1
        ];
        $stockSku = json_encode($stockSku);
        $stockApiData = $this->multiPriceAndStock->getMultiStockAndPriceData($stockSku, 'stock');

        if (!empty($stockApiData)) {
            $stockApiData = json_decode($stockApiData, true);
            $stockQty = isset($stockApiData[0]['AvailableQuantity'])
                ? $stockApiData[0]['AvailableQuantity']
                : 0;
            if ($stockQty < $qty) {
                $minimumAndMasureQty = $this->checkoutHelper->getMinimumAndMeasureQty($product);
                $newQty = $this->getNextMinimumQty($minimumAndMasureQty, $stockQty);
                $newQty = (float)$newQty * (float)$minimumAndMasureQty;
            }
        }
        return $newQty;
    }

    /**
     * @param $minimumAndMasureQty
     * @param $userQty
     * @return int
     */
    public function getNextMinimumQty($minimumAndMasureQty, $userQty)
    {
        if ($userQty && $minimumAndMasureQty) {
            $minimumQty = (int)($userQty / $minimumAndMasureQty);
            return ($minimumQty > 0) ? $minimumQty : 1;
        }
        return 1;
    }

    /**
     * @param $productCode
     * @return DataObject
     */
    public function getProductByProductCode($productCode)
    {
        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('product_code', ['eq' => $productCode])
            ->getFirstItem();
    }
}
