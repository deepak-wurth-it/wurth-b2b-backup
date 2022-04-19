<?php

namespace Wcb\NegotiableQuote\Block;

use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as NegotiableQuoteCollection;
use Magento\Quote\Model\QuoteFactory;

class RequestQuote extends Template
{
    protected $negotiableQuoteCollection;
    protected $quoteFactory;
    protected $imageHelper;
    protected $customerSession;

    public function __construct(
        Context $context,
        NegotiableQuoteCollection $negotiableQuoteCollection,
        QuoteFactory $quoteFactory,
        Image $imageHelper,
        SessionFactory $customerSession
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->negotiableQuoteCollection = $negotiableQuoteCollection;
        $this->imageHelper = $imageHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getQuoteUrl()
    {
        return $this->getUrl("negotiable_quote/quote");
    }

    public function getNegotiableQuoteCollection()
    {
        $customerId = $this->customerSession->create()->getCustomer()->getId();

        $collection = $this->negotiableQuoteCollection->create()
            ->addFieldToFilter("customer_id", ['eq' => $customerId]);
        $quoteComment = $collection->getTable('negotiable_quote_comment');

        $collection->getSelect()->join(
            ['quote_comment' => $quoteComment],
            'main_table.entity_id = quote_comment.parent_id'
        );
        return $collection;
    }

    public function getDateFormat($dateTime)
    {
        return date('d.m.Y', strtotime($dateTime));
    }

    public function getQuoteCollectionById($quoteId)
    {
        return $this->quoteFactory->create()->load($quoteId);
    }

    public function getItemImage($item)
    {
        return $this->imageHelper->init($item, 'product_base_image')->getUrl();
    }
}
