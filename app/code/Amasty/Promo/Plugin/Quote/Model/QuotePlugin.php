<?php

namespace Amasty\Promo\Plugin\Quote\Model;

use Amasty\Promo\Model\Storage;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

/**
 * Save quote after update quote items if necessary
 */
class QuotePlugin
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    public function __construct(
        Storage $storage,
        QuoteRepository $quoteRepository
    ) {
        $this->storage = $storage;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Save quote if items was updated and current process is not going to save quote.
     * Extra quote saves can cause Magento errors.
     *
     * @param Quote $subject
     * @param Quote $result
     *
     * @return Quote
     */
    public function afterCollectTotals($subject, $result)
    {
        if (!$subject->getTriggerRecollect()
            && $this->storage->isQuoteSaveAllowed()
            && $this->storage->isQuoteSaveRequired()
        ) {
            $this->storage->setIsQuoteSaveRequired(false);
            $this->quoteRepository->save($subject);
        }

        return $result;
    }
}
