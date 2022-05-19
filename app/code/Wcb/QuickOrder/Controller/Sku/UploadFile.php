<?php

namespace Wcb\QuickOrder\Controller\Sku;

use Magento\AdvancedCheckout\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\QuickOrder\Model\Config as ModuleConfig;
use Wcb\Checkout\Helper\Data as checkoutHelper;
use Wcb\QuickOrder\Helper\Data as QuickOrderHelper;

/**
 * Class for processing file upload.
 */
class UploadFile extends \Magento\QuickOrder\Controller\Sku\UploadFile
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    protected $checkoutHelper;
    protected $quickOrderHelper;

    /**
     * UploadFile constructor.
     * @param Context $context
     * @param ModuleConfig $moduleConfig
     * @param Data $advancedCheckoutHelper
     * @param CollectionFactory $productCollectionFactory
     * @param checkoutHelper $checkoutHelper
     * @param QuickOrderHelper $quickOrderHelper
     */
    public function __construct(
        Context $context,
        ModuleConfig $moduleConfig,
        Data $advancedCheckoutHelper,
        CollectionFactory $productCollectionFactory,
        checkoutHelper $checkoutHelper,
        QuickOrderHelper $quickOrderHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->checkoutHelper = $checkoutHelper;
        $this->quickOrderHelper = $quickOrderHelper;
        parent::__construct($context, $moduleConfig, $advancedCheckoutHelper);
    }

    /**
     * Upload file action.
     *
     * @return void
     */
    public function execute()
    {
        $rows = $this->advancedCheckoutHelper->isSkuFileUploaded($this->getRequest())
            ? $this->advancedCheckoutHelper->processSkuFileUploading()
            : [];

        $items = $this->getRequest()->getPost('items');
        if (!is_array($items)) {
            $items = [];
        }

        if (is_array($rows) && count($rows)) {
            foreach ($rows as $row) {
                $items[] = $row;
            }
        }
        $updatedItems = [];

        //Get sku using product code and set so magento default working as it as

        foreach ($items as $_item) {
            $product = $this->getProductByProductCode($_item['sku']);
            if ($product->getSku()) {
                $_item['sku'] = $product->getSku();
            }
            if (!isset($_item['qty'])) {
                $_item['qty'] = 1;
            }
            $minimumAndMasureQty = $this->checkoutHelper->getMinimumAndMeasureQty($product);

            $newQty = $this->getNextMinimumQty($minimumAndMasureQty, $_item['qty']);
            $_item['qty'] = $newQty;
            $updatedItems[] = $_item;
        }
        if (!empty($updatedItems)) {
            $items = $updatedItems;
        }
        //End Get sku using product code and set so magento default working as it as
        $this->getRequest()->setParam('items', $items);
        $this->_forward('advancedAdd', 'cart', 'customer_order');
    }

    /**
     * @param $productCode
     * @return DataObject
     */
    public function getProductByProductCode($productCode)
    {
        $productIds = $this->quickOrderHelper->getProductCodeWithProductId($productCode, false);

        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->getFirstItem();
    }
    public function getNextMinimumQty($minimumAndMasureQty, $userQty)
    {
        if ($userQty && $minimumAndMasureQty) {
            $minimumQty = (int) ($userQty / $minimumAndMasureQty);
            return ($minimumQty > 0) ? $minimumQty : 1;
        }
        return 1;
    }
}
