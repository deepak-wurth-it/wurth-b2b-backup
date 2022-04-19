<?php

namespace Wcb\NegotiableQuote\Model;

use Exception;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Model\CommentManagementInterface;
use Magento\NegotiableQuote\Model\Email\Sender;
use Magento\NegotiableQuote\Model\NegotiableQuoteConverter;
use Magento\NegotiableQuote\Model\Quote\History;
use Magento\NegotiableQuote\Model\QuoteUpdater;
use Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Wurth\Shippingproduct\Helper\Data as ShippingProductHelper;

/**
 * Class for managing negotiable quotes.
 */
class NegotiableQuoteManagement extends \Magento\NegotiableQuote\Model\NegotiableQuoteManagement
{
    /**
     * @var CustomerCart
     */
    protected $cart;
    /**
     * @var ShippingProductHelper
     */
    protected $shippingProductHelper;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var Sender
     */
    private $emailSender;
    /**
     * @var CommentManagementInterface
     */
    private $commentManagement;
    /**
     * @var NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;
    /**
     * @var NegotiableQuoteConverter
     */
    private $negotiableQuoteConverter;
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;
    /**
     * @var History
     */
    private $quoteHistory;
    /**
     * @var ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * NegotiableQuoteManagement constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param Sender $emailSender
     * @param CommentManagementInterface $commentManagement
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param NegotiableQuoteConverter $negotiableQuoteConverter
     * @param QuoteUpdater $quoteUpdater
     * @param History $quoteHistory
     * @param ValidatorInterfaceFactory $validatorFactory
     * @param CustomerCart $cart
     * @param ShippingProductHelper $shippingProductHelper
     * @param Registry $registry
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Sender $emailSender,
        CommentManagementInterface $commentManagement,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        NegotiableQuoteConverter $negotiableQuoteConverter,
        QuoteUpdater $quoteUpdater,
        History $quoteHistory,
        ValidatorInterfaceFactory $validatorFactory,
        CustomerCart $cart,
        ShippingProductHelper $shippingProductHelper,
        Registry $registry
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->emailSender = $emailSender;
        $this->commentManagement = $commentManagement;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->negotiableQuoteConverter = $negotiableQuoteConverter;
        $this->quoteUpdater = $quoteUpdater;
        $this->quoteHistory = $quoteHistory;
        $this->validatorFactory = $validatorFactory;
        $this->cart = $cart;
        $this->shippingProductHelper = $shippingProductHelper;
        $this->registry = $registry;
        parent::__construct(
            $quoteRepository,
            $emailSender,
            $commentManagement,
            $quoteItemManagement,
            $negotiableQuoteConverter,
            $quoteUpdater,
            $quoteHistory,
            $validatorFactory
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create($quoteId, $quoteName, $commentText = '', array $files = [])
    {
        $quote = $this->retrieveQuote($quoteId);
        $validator = $this->validatorFactory->create(['action' => 'create']);
        $validateResult = $validator->validate(['quote' => $quote, 'files' => $files]);
        if ($validateResult->hasMessages()) {
            $exception = new InputException(__('Cannot create a B2B quote.'));
            foreach ($validateResult->getMessages() as $message) {
                $exception->addError($message);
            }
            throw $exception;
        }
        $this->removeCartDiscounts($quote);
        $this->removeShippingProduct($quote, $quoteId);
        $quote->collectTotals();

        $this->quoteUpdater->updateCurrentDate($quote);
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $negotiableQuote->setQuoteId($quote->getId())
            ->setIsRegularQuote(true)
            ->setAppliedRuleIds($quote->getAppliedRuleIds())
            ->setStatus(NegotiableQuoteInterface::STATUS_CREATED)
            ->setQuoteName($quoteName);
        $this->quoteRepository->save($quote);
        $this->quoteItemManagement->updateQuoteItemsCustomPrices($quoteId);
        $this->commentManagement->update(
            $quoteId,
            $commentText,
            $files
        );
        $this->quoteHistory->createLog($quoteId);
        $this->emailSender->sendChangeQuoteEmailToMerchant(
            $quote,
            Sender::XML_PATH_SELLER_NEW_QUOTE_CREATED_BY_BUYER_TEMPLATE
        );
        return true;
    }

    /**
     * Retrieve quote from repository.
     *
     * @param int $quoteId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function retrieveQuote($quoteId)
    {
        try {
            return $this->quoteRepository->get($quoteId, ['*']);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(
                __(
                    'Requested quote is not found. Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'quoteId', 'fieldValue' => $quoteId]
                )
            );
        }
    }

    /**
     * Remove cart discounts on negotiable quote.
     *
     * @param CartInterface $quote
     * @return $this
     */
    private function removeCartDiscounts(CartInterface $quote)
    {
        if ($quote->getGiftCards() !== null) {
            $quote->setGiftCards(null);
        }

        if ($quote->getCouponCode() !== null) {
            $quote->setCouponCode(null);
        }

        return $this;
    }

    /**
     * @param $quote
     * @param $quoteId
     */
    public function removeShippingProduct($quote, $quoteId)
    {
        $shippingProductCode = $this->shippingProductHelper->getConfig(ShippingProductHelper::SHIPPING_PRODUCT_CODE);
        try {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getProduct()->getProductCode() === $shippingProductCode) {
                    $this->registry->register('skip_plugin', 'true');
                    $this->cart->removeItem($item->getId());
                    $this->cart->save();
                    $this->cart->getQuote()->setTriggerRecollect(1);
                    $this->cart->getQuote()->collectTotals()->save();
                    $this->registry->register('skip_plugin', 'false');
                }
            }
        } catch (Exception $e) {
        }
    }
}
