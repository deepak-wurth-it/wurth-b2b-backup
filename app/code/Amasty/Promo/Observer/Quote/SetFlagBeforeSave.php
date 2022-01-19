<?php
declare(strict_types=1);

namespace Amasty\Promo\Observer\Quote;

use Amasty\Promo\Model\Storage;
use Magento\Framework\Event\ObserverInterface;

/**
 * event name checkout_cart_save_before
 * Avoid quote double save. Set restriction flag to save quote by Free Gift.
 */
class SetFlagBeforeSave implements ObserverInterface
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Restrict extra quote save by Free Gift.
     *
     * Reverse process (Allowing quote save) is in plugin
     * @see \Amasty\Promo\Plugin\Quote\Model\QuoteRepository\SaveHandlerPlugin::afterSave
     *
     * Extra quote saves can cause magento errors.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $this->storage->restrictQuoteSaving();
    }
}
