<?php

namespace Wcb\CouponCodes\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class AssignProducts extends Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $prodCollFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context                                         $context
     * @param Filter                                          $filter
     * @param CollectionFactory                               $prodCollFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $prodCollFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->filter = $filter;
        $this->prodCollFactory = $prodCollFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        $salableValue = $this->getRequest()->getParam('free_shipping_product');
        $collection = $this->filter->getCollection($this->prodCollFactory->create());
        foreach ($collection->getAllIds() as $productId)
        {
            $productDataObject = $this->productRepository->getById($productId);
            $productDataObject->setData('free_shipping_product',$salableValue);
            $this->productRepository->save($productDataObject);
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', $collection->getSize()));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/product/index');
    }
}