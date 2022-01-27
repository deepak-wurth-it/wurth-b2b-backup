<?php
namespace Amasty\Promo\Block;

/**
 * Popup with Promo Items initialization and link for open
 */
class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Amasty\Promo\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->promoHelper = $promoHelper;
        $this->urlHelper = $urlHelper;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return (bool)$this->promoHelper->getNewItems();
    }

    /**
     * @return string
     * @deprecated since 2.5.0
     */
    public function getMessage()
    {
        return $this->getPopupLinkHtml();
    }

    /**
     * @return string
     */
    public function getPopupLinkHtml()
    {
        return $this->config->getAddMessage();
    }

    /**
     * @return bool
     */
    public function isOpenAutomatically()
    {
        return $this->config->isAutoOpenPopup() && $this->hasItems();
    }

    /**
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return $this->urlHelper->getCurrentBase64Url();
    }

    /**
     * @return array
     */
    public function getAvailableProductQty()
    {
        return $this->promoHelper->getPromoItemsDataArray();
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('amasty_promo/cart/add');
    }

    /**
     * @return int|null
     */
    public function getSelectionMethod()
    {
        return $this->config->getSelectionMethod();
    }

    /**
     * Is gift counter visible
     *
     * @return int|null
     */
    public function getGiftsCounter()
    {
        return $this->config->getGiftsCounter();
    }
}
