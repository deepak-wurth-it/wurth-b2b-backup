<?php

namespace Wurth\Theme\Controller\Checkout;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\QuoteRepository;

class SaveInQuote extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * SaveInQuote constructor.
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param LayoutFactory $layoutFactory
     * @param Cart $cart
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        LayoutFactory $layoutFactory,
        Cart $cart,
        Session $checkoutSession,
        QuoteRepository $quoteRepository
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layoutFactory = $layoutFactory;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $confirmationEmail = $this->getRequest()->getParam('confirmation_email');
            $internalOrderNumber = $this->getRequest()->getParam('internal_order_numer');
            $remarks = $this->getRequest()->getParam('remarks');

            $quoteId = $this->checkoutSession->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $quote->setOrderConfirmationEmail($confirmationEmail);
            $quote->setInternalOrderNumber($internalOrderNumber);
            $quote->setRemarks($remarks);
            $quote->setDeliveryOrder(0);
            $quote->save();
        } catch (Exception $e) {
        }
    }
}
