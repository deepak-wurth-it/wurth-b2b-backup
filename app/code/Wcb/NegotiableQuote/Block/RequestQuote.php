<?php

namespace Wcb\NegotiableQuote\Block;

use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as NegotiableQuoteCollection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Wcb\Customer\Helper\Data as CustomerHelper;

class RequestQuote extends Template
{
    /**
     * @var NegotiableQuoteCollection
     */
    protected $negotiableQuoteCollection;
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var Image
     */
    protected $imageHelper;
    /**
     * @var SessionFactory
     */
    protected $customerSession;
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * RequestQuote constructor.
     * @param Context $context
     * @param NegotiableQuoteCollection $negotiableQuoteCollection
     * @param QuoteFactory $quoteFactory
     * @param Image $imageHelper
     * @param SessionFactory $customerSession
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        Context $context,
        NegotiableQuoteCollection $negotiableQuoteCollection,
        QuoteFactory $quoteFactory,
        Image $imageHelper,
        SessionFactory $customerSession,
        CustomerHelper $customerHelper
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->customerHelper = $customerHelper;
        $this->negotiableQuoteCollection = $negotiableQuoteCollection;
        $this->imageHelper = $imageHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getQuoteUrl()
    {
        return $this->getUrl("negotiable_quote/quote");
    }

    /**
     * @param $dateTime
     * @return false|string
     */
    public function getDateFormat($dateTime)
    {
        return date('d.m.Y', strtotime($dateTime));
    }

    /**
     * @param $quoteId
     * @return Quote
     */
    public function getQuoteCollectionById($quoteId)
    {
        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemImage($item)
    {
        return $this->imageHelper->init($item, 'product_base_image')->getUrl();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return $this|RequestQuote
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Custom Pagination'));
        if ($this->getNegotiableQuoteCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'quote.history.pager'
            )->setAvailableLimit([10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)->setCollection(
                    $this->getNegotiableQuoteCollection()
                );
            $this->setChild('pager', $pager);
            $this->getNegotiableQuoteCollection()->load();
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNegotiableQuoteCollection()
    {
        $customerCode = $this->getCustomerCode();
        $customerIds = [];
        if ($customerCode != '') {
            $sameCustomerCodeCustomers = $this->customerHelper->getCustomerByCustomerCode($customerCode);
            $customerIds = $sameCustomerCodeCustomers->getAllIds();
        }

        $page = $this->getRequest()->getParam('p');
        $pageSize = $this->getRequest()->getParam('limit');
        $page = ($page) ? $page : 1;
        $pageSize = ($pageSize) ? $pageSize : 10;

        $customerId = $this->customerSession->create()->getCustomer()->getId();
        $collection = $this->negotiableQuoteCollection->create()
            ->addFieldToFilter("customer_id", ['in' => $customerIds]);
        $quoteComment = $collection->getTable('negotiable_quote_comment');

        $collection->getSelect()->join(
            ['quote_comment' => $quoteComment],
            'main_table.entity_id = quote_comment.parent_id'
        );
        $collection->setCurPage($page);
        $collection->setPageSize($pageSize);

        $collection->setOrder('main_table.created_at', 'desc');
        return $collection;
    }

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

    public function getCurrentCustomer()
    {
        return $this->customerHelper->getCurrentCustomer();
    }
}
