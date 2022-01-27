<?php

namespace Amasty\Promo\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;

/**
 * Popup Style
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    const POPUP_ONE_BY_ONE = 0;
    const POPUP_MULTIPLE = 1;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $modelConfig;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var Add
     */
    private $promoAddBlock;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Template\Context $context,
        \Amasty\Promo\Model\Config $modelConfig,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Amasty\Promo\Block\Add $promoAddBlock,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->modelConfig = $modelConfig;
        $this->promoHelper = $promoHelper;
        $this->promoAddBlock = $promoAddBlock;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return Add
     */
    public function getPromoAddBlock()
    {
        return $this->promoAddBlock;
    }

    /**
     * @return int|null
     */
    public function getCountersMode()
    {
        return $this->modelConfig->getScopeValue("messages/display_remaining_gifts_counter");
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getPopupName()
    {
        $popupTitle = $this->modelConfig->getPopupName();

        if (!$popupTitle) {
            $popupTitle = __('Free Items');
        }

        return $popupTitle;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $newItems = $this->promoHelper->getNewItems();

        return $newItems ? $newItems->getSize() : 0;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasQuoteItems(): bool
    {
        return (bool)$this->checkoutSession->getQuote()->getAllVisibleItems();
    }
}
