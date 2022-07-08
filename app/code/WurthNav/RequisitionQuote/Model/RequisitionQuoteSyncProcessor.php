<?php

namespace WurthNav\RequisitionQuote\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as NegotiableQuoteCollection;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory as MagentoQuoteFactory;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class RequisitionQuoteSyncProcessor
{
    /**
     * @var NegotiableQuoteCollection
     */
    protected $negotiableQuoteCollection;
    /**
     * @var QuotesFactory
     */
    protected $quotes;
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;
    /**
     * @var MagentoQuoteFactory
     */
    protected $magentoQuote;
    /**
     * @var QuotesLineFactory
     */
    protected $quotesLineFactory;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * RequisitionQuoteSyncProcessor constructor.
     * @param NegotiableQuoteCollection $negotiableQuoteCollection
     * @param QuotesFactory $quotes
     * @param QuotesLineFactory $quotesLineFactory
     * @param CustomerRepository $customerRepository
     * @param DateTimeFactory $dateTimeFactory
     * @param MagentoQuoteFactory $magentoQuote
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        NegotiableQuoteCollection $negotiableQuoteCollection,
        QuotesFactory $quotes,
        QuotesLineFactory $quotesLineFactory,
        CustomerRepository $customerRepository,
        DateTimeFactory $dateTimeFactory,
        MagentoQuoteFactory $magentoQuote,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->quotes = $quotes;
        $this->negotiableQuoteCollection = $negotiableQuoteCollection;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->magentoQuote = $magentoQuote;
        $this->quotesLineFactory = $quotesLineFactory;
        $this->quoteRepository = $quoteRepository;
    }

    public function install()
    {
        try {
            $quoteCollection = $this->getNegotiableQuoteCollection();
            foreach ($quoteCollection as $item) {
                $customer = $this->getCustomerCodeById($item->getCustomerId());

                $navQuote = $this->setNavQuote($customer, $item);
                if ($navQuote->getId()) {
                    $this->setQuoteItems($navQuote->getId(), $item->getParentId());
                }
                $this->changeQuoteSyncStatus($item->getParentId());
            }
        } catch (Exception $e) {
            $this->wurthNavLogger($e->getMessage());
        }
    }

    /**
     * @return Collection
     */
    public function getNegotiableQuoteCollection()
    {
        $collection = $this->negotiableQuoteCollection->create()
            ->addFieldToFilter("wcb_nav_sync", ["eq" => 0]);
        $quoteComment = $collection->getTable('negotiable_quote_comment');

        $collection->getSelect()->join(
            ['quote_comment' => $quoteComment],
            'main_table.entity_id = quote_comment.parent_id'
        );
        return $collection;
    }

    /**
     * @param $customerId
     * @return bool|CustomerInterface
     */
    public function getCustomerCodeById($customerId)
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $customer
     * @param $item
     * @return Quotes
     */
    public function setNavQuote($customer, $item)
    {
        try {
            $customerCode = "";
            $createdBy = "";
            $email = "";

            if ($customer) {
                if ($customer->getCustomAttribute("customer_code")) {
                    $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
                }
                $createdBy = $customer->getFirstName() . " " . $customer->getLastName();
                $email = $customer->getEmail();
            }

            $navQuote = $this->quotes->create();
            $navQuote->setData("Document_No", $item->getParentId());
            $navQuote->setData("Customer_Code", $customerCode);
            $navQuote->setData("Created_by", $createdBy);
            $navQuote->setData("Creation_date", $item->getCreatedAt());
            $navQuote->setData("Email", $email);
            $navQuote->setData("Comment", $item->getComment());
            $navQuote->save();
            return $navQuote;
        } catch (Exception $e) {
            $this->wurthNavLogger($e->getMessage());
        }
    }

    /**
     * @param $log
     */
    public function wurthNavLogger($log)
    {
        $writer = new Stream(BP . '/var/log/wurthnav_requisition_quote_sync.log');
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }

    /**
     * @param $navQuoteId
     * @param $quoteId
     */
    public function setQuoteItems($navQuoteId, $quoteId)
    {
        try {
            $quoteItems = $this->quoteRepository->get($quoteId)->getAllVisibleItems();
            $i = 1;
            foreach ($quoteItems as $quoteItem) {
                $quoteLine = $this->quotesLineFactory->create();
                $quoteLine->setData('QuoteId', $navQuoteId);
                $quoteLine->setData('Document_No', $quoteId);
                $quoteLine->setData('Line_No', $i);
                $quoteLine->setData('Item_No', $quoteItem->getProduct()->getProductCode());
                $quoteLine->setData('Quantity', $quoteItem->getQty());
                $quoteLine->save();
                $i++;
            }
        } catch (Exception $e) {
            $this->wurthNavLogger($e->getMessage());
        }
    }

    /**
     * @param $quoteId
     */
    public function changeQuoteSyncStatus($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote->getId()) {
            $quote->setData('wcb_nav_sync', 1);
            $this->quoteRepository->save($quote);
            $this->wurthNavLogger("successfully sync quote -" . $quoteId);
        }
    }

    /**
     * @param $date
     * @return string
     */
    public function getDateFormat($date)
    {
        $dateModel = $this->dateTimeFactory->create();
        return $dateModel->date('d.m.Y H:i', $date);
    }
}
