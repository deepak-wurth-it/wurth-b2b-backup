<?php

namespace Amasty\Promo\Helper;

/**
 * Promo Messages for customer
 */
class Messages extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $resourceSession;
        $this->messageManager = $messageManager;
    }

    public function addAvailabilityError($product)
    {
        $this->showMessage(
            __(
                "We apologize, but your free gift <strong>%1</strong> is not available at the moment",
                $product->getName()
            )
        );
    }

    /**
     * @param string|\Magento\Framework\Phrase $message
     * @param bool $isError
     * @param bool $showEachTime
     * @param bool $isSuccess
     */
    public function showMessage($message, $isError = true, $showEachTime = false, $isSuccess = false)
    {
        $displayErrors = $this->scopeConfig->isSetFlag(
            'ampromo/messages/display_error_messages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$displayErrors && $isError) {
            return;
        }

        $displaySuccess = $this->scopeConfig->isSetFlag(
            'ampromo/messages/display_success_messages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$displaySuccess && !$isError) {
            return;
        }

        $all = $this->messageManager->getMessages(false);

        foreach ($all as $existingMessage) {
            if ($message == $existingMessage->getText()) {
                return;
            }
        }

        if ($isError && $this->_request->getParam('debug')) {
            // method addErrorMessage is not applicable because of html escape
            $this->messageManager->addError($message);
        } elseif ($showEachTime || !$this->isMessageWasShown($message)) {
            if ($isSuccess) {
                $this->messageManager->addSuccess($message);
            } else {
                // method addNoticeMessage is not applicable because of html escape
                $this->messageManager->addNotice($message);
            }
        }
    }

    /**
     * @param string|\Magento\Framework\Phrase $message
     *
     * @return bool
     */
    private function isMessageWasShown($message)
    {
        if ($message instanceof \Magento\Framework\Phrase) {
            $messageText = $message->getText();
        } else {
            $messageText = $message;
        }
        $arr = $this->_checkoutSession->getAmpromoMessages();
        if (!is_array($arr)) {
            $arr = [];
        }
        if (!in_array($messageText, $arr)) {
            $arr[] = $messageText;
            $this->_checkoutSession->setAmpromoMessages($arr);

            return false;
        }

        return true;
    }
}
