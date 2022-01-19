<?php

namespace Amasty\Promo\Model;

/**
 * local registry
 */
class Storage
{
    /**
     * Free gift quote items with tax
     *
     * @var array
     * @deprecated
     */
    public static $cachedFreeGiftsWithTax = [];

    /**
     * Cached price with tax for free gift quote items
     *
     * @var array
     * @deprecated
     */
    public static $cachedQuoteItemPricesWithTax = [];

    /**
     * @var bool
     */
    public static $isReorder = false;

    /**
     * @var bool
     */
    private $isQuoteSaveAllowed = true;

    /**
     * @var bool
     */
    private $isQuoteSaveRequired = false;

    /**
     * @var bool
     */
    private $isAutoAddAllowed = true;

    /**
     * Set flag for avoid quote saving while Free Gift adding
     * Extra quote saves can cause Magento errors.
     */
    public function restrictQuoteSaving()
    {
        $this->isQuoteSaveAllowed = false;
    }

    public function allowQuoteSaving()
    {
        $this->isQuoteSaveAllowed = true;
    }

    /**
     * Is collect totals process runned from save handler (it means we shouldn't save quote)
     *
     * @return bool
     */
    public function isQuoteSaveAllowed(): bool
    {
        return $this->isQuoteSaveAllowed;
    }

    /**
     * Is quote items was updated and changes should be saved
     *
     * @return bool
     */
    public function isQuoteSaveRequired(): bool
    {
        return $this->isQuoteSaveRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsQuoteSaveRequired(bool $isRequired)
    {
        $this->isQuoteSaveRequired = $isRequired;
    }

    /**
     * Is quote items was updated and changes should be saved
     *
     * @return bool
     */
    public function isAutoAddAllowed(): bool
    {
        return $this->isAutoAddAllowed;
    }

    /**
     * @param bool $flag
     */
    public function setIsAutoAddAllowed(bool $flag)
    {
        $this->isAutoAddAllowed = $flag;
    }
}
