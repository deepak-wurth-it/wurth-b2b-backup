<?php

namespace Wcb\RequisitionList\Block;

use DateTime;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Collection;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\CollectionFactory as RequisitionListFactory;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\CollectionFactory as RequisitionItemsFactory;
use Wcb\Checkout\Helper\Data as CheckoutHelper;
use Wcb\Checkout\Helper\MultiPriceAndStock;
use Wcb\Customer\Helper\Data as CustomerHelper;

class RequisitionList extends Template
{
    protected $requisitionList;
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    protected $storeCustomer;
    protected $storeProducts;
    /**
     * @var RequisitionItemsFactory
     */
    protected $requisitionItems;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    protected $imageHelper;
    protected $multiPriceAndStock;
    protected $checkoutHelper;

    /**
     * RequisitionList constructor.
     * @param Template\Context $context
     * @param RequisitionListFactory $requisitionListFactory
     * @param TimezoneInterface $timezone
     * @param CustomerHelper $customerHelper
     * @param RequisitionItemsFactory $requisitionItemsFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Image $imageHelper
     * @param MultiPriceAndStock $multiPriceAndStock
     * @param CheckoutHelper $checkoutHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        RequisitionListFactory $requisitionListFactory,
        TimezoneInterface $timezone,
        CustomerHelper $customerHelper,
        RequisitionItemsFactory $requisitionItemsFactory,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        MultiPriceAndStock $multiPriceAndStock,
        CheckoutHelper $checkoutHelper,
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->customerHelper = $customerHelper;
        $this->requisitionList = $requisitionListFactory;
        $this->requisitionItems = $requisitionItemsFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->multiPriceAndStock = $multiPriceAndStock;
        $this->checkoutHelper = $checkoutHelper;
        parent::__construct($context, $data);
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCompanyAndCustomerCode()
    {
        $customerCode = "";
        $companyName = "";
        $customer = $this->getCurrentCustomer();
        if ($customer) {
            $company = $this->customerHelper->getCompany($customer);
            if ($customer->getCustomAttribute("customer_code")) {
                $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
            }
            if ($company) {
                $companyName = $company->getCompanyName();
            }
        }
        return [
            "customer_code" => $customerCode,
            "company_name" => $companyName,
        ];
    }

    /**
     * @return bool|CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomer()
    {
        return $this->customerHelper->getCurrentCustomer();
    }

    /**
     * @param $dateTime
     * @return false|string
     */
    public function getCreateDateFormat($dateTime)
    {
        return date('d/m/Y', strtotime($dateTime));
    }

    /**
     * @param $dateTime
     * @return string
     */
    public function getCreateDateTimeFormat($dateTime)
    {
        return $this->timezone->date(new DateTime($dateTime))->format('Y.m.d H:i');
    }

    /**
     * @param $customerId
     * @return stringwc
     */
    public function getCustomerName($customerId)
    {
        $customerName = '';
        if (isset($this->storeCustomer[$customerId])) {
            $customerName = $this->storeCustomer[$customerId];
        }
        return $customerName;
    }

    /**
     * @param $listId
     * @return \Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\Collection
     */
    public function getRequisitionListItems($listId)
    {
        return $this->requisitionItems->create()
            ->addFieldToFilter("requisition_list_id", ['eq' => $listId]);
    }

    public function getProductBySku($sku)
    {
        try {
            if (isset($this->storeProducts[$sku])) {
                $product = $this->storeProducts[$sku];
            } else {
                $product = $this->productRepository->get($sku);
                $this->storeProducts[$sku] = $product;
            }
            return $product;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Pagination'));
        if ($this->getRequisitionList()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.requisition.pager'
            )->setAvailableLimit([10 => 10, 15 => 15, 20 => 20, 50 => 50])
                ->setShowPerPage(true)->setCollection(
                    $this->getRequisitionList()
                );
            $this->setChild('pager', $pager);
            $this->getRequisitionList()->load();
        }
        return $this;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRequisitionList()
    {
        $customerCode = $this->getCustomerCode();
        $sameCustomerCodeCustomers = $this->customerHelper->getCustomerByCustomerCode($customerCode);
        foreach ($sameCustomerCodeCustomers as $_customer) {
            $this->storeCustomer[$_customer->getId()] = $_customer->getName();
        }
        $currentPage = $this->getRequest()->getParam('p');
        $currentLimit = $this->getRequest()->getParam('limit');
        $page = ($currentPage) ? $currentPage : 1;
        $pageSize = ($currentLimit) ? $currentLimit : 10;

        return $this->requisitionList->create()
            ->addFieldToFilter("customer_id", ["in", $sameCustomerCodeCustomers->getAllIds()])
            ->setOrder('entity_id', 'DESC')
            ->setPageSize($pageSize)
            ->setCurPage($page);
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerCode()
    {
        $customerCode = "";
        $customer = $this->getCurrentCustomer();
        if ($customer) {
            if ($customer->getCustomAttribute("customer_code")) {
                $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
            }
        }
        return $customerCode;
    }
    public function getProductImage($product)
    {
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }
    public function getStockDataByProductCode($product, $qty)
    {
        try {
            $stockData = [];
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
                $stockData['color'] = $this->getStockColor($qty, $stockQty);
                $stockData['avail_qty'] = $stockQty;
            }
            return $stockData;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getStockColor($qty, $availableQty)
    {
        if ($qty < $availableQty) {
            return "green";
        }
        if ($qty == $availableQty) {
            return "yellow";
        }
        if ($qty > $availableQty) {
            return "blue";
        }
        if ($availableQty == 0) {
            return "red";
        }
        return '';
    }
    public function getMinimumAndMeasureQty($product)
    {
        return $this->checkoutHelper->getMinimumAndMeasureQty($product);
    }
    public function getQuantityUnitByQuantity($qty, $product)
    {
        return $this->checkoutHelper->getQuantityUnitByQuantity($qty, $product);
    }
}
