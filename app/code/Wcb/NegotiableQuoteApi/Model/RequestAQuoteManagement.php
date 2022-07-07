<?php
namespace Wcb\NegotiableQuoteApi\Model;

use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\CustomerFactory;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as NegotiableQuoteCollection;
use Magento\Quote\Model\QuoteFactory;
use Wcb\NegotiableQuoteApi\Api\RequestAQuoteManagementInterface;
use Wcb\Customer\Helper\Data as CustomerHelper;
class RequestAQuoteManagement implements RequestAQuoteManagementInterface
{
    /**
     * @var NegotiableQuoteCollection
     */
    private $negotiableQuoteCollection;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var Image
     */
    private $imageHelper;
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * RequestAQuoteManagement constructor.
     * @param NegotiableQuoteCollection $negotiableQuoteCollection
     * @param QuoteFactory $quoteFactory
     * @param Image $imageHelper
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        NegotiableQuoteCollection $negotiableQuoteCollection,
        QuoteFactory $quoteFactory,
        Image $imageHelper,
        CustomerHelper $customerHelper
    ) {
        $this->negotiableQuoteCollection = $negotiableQuoteCollection;
        $this->quoteFactory = $quoteFactory;
        $this->imageHelper = $imageHelper;
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param int $customerId
     * @param string $customer_code
     * @return mixed|void
     */
    public function getNegotiableQuoteList($customerId,$customer_code)
    {
        $result = [];
        $customers = [];
        try {
            $customer_codes =  $this->customerHelper->getCustomerByCustomerCode($customer_code);
            foreach ($customer_codes as $code){
                $customers[] = $code->getId();
            }
            $collection = $this->getNegotiableQuoteCollection($customers);
            foreach ($collection as $commentCollection) {
                $commentsItem = [];
                $commentsItem['inquiry_id'] = $commentCollection->getParentId();
                $commentsItem['created_at'] = $this->getDateFormat($commentCollection->getCreatedAt());
                $commentsItem['comment'] = $commentCollection->getComment();
                $commentsItem['item_count'] = $commentCollection->getItemsCount();
                $commentsItem['customer_id'] = $commentCollection->getCustomerId();
                $commentsItem['items'] = $this->getQuoteCollectionById($commentCollection->getParentId());
                $result[] =$commentsItem;
            }
            return $result;
        } catch (\Exception $e) {
        }
    }

    public function getNegotiableQuoteCollection($customerIds)
    {
        $collection = $this->negotiableQuoteCollection->create()
            ->addFieldToFilter("customer_id", ['in' => $customerIds]);
//            ->setOrder("ASC")
//            ->setPageSize(10)
//            ->setCurPage(1);

        $quoteComment = $collection->getTable('negotiable_quote_comment');

        $collection->getSelect()->join(
            ['quote_comment' => $quoteComment],
            'main_table.entity_id = quote_comment.parent_id'
        );
        return $collection;
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
     * @return array
     */
    public function getQuoteCollectionById($quoteId)
    {
        $data = [];
        $quoteItemsCollection =  $this->quoteFactory->create()->load($quoteId)->getAllVisibleItems();
        foreach ($quoteItemsCollection as $item) {
            $tmpData= [];
            $tmpData['thumbnail'] = $item->getProduct()->getThumbnail();
            $tmpData['small_image'] = $item->getProduct()->getSmallImage();
            $tmpData['product_code'] = $item->getProduct()->getProductCode();
            $tmpData['name'] = $item->getName();
            $tmpData['sku'] = $item->getSku();
            $tmpData['qty'] = $item->getQty();
            $tmpData['created_at'] = $this->getDateFormat($item->getCreatedAt());
            $data[]= $tmpData;
        }
        return $data;
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemImage($item)
    {
        return $this->imageHelper->init($item, 'product_base_image')->getUrl();
    }
}
