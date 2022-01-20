<?php
namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * observer name  Amasty_Promo::setMarkToQuoteItem
 * event name     checkout_cart_product_add_after
 */
class SetMarkToQuoteItem implements ObserverInterface
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helper;

    /**
     * @var \Amasty\Promo\Api\Data\TotalsItemImageInterfaceFactory
     */
    private $extensionAttributeFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * SetMarkToQuoteItem constructor.
     *
     * @param \Amasty\Promo\Helper\Item $helper
     */
    public function __construct(
        \Amasty\Promo\Helper\Item $helper,
        \Amasty\Promo\Api\Data\TotalsItemImageInterfaceFactory $extensionAttributeFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->extensionAttributeFactory = $extensionAttributeFactory;
        $this->imageHelper = $imageHelper;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getItem();
        if ($this->helper->isPromoItem($quoteItem)) {
            $image = $this->getImageData($quoteItem);
            $quoteItem->setAmastyImagePath($image);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return array
     */
    private function getImageData($quoteItem)
    {
        // start emulate frontend, for get frontend urls for product images
        $this->appEmulation->startEnvironmentEmulation(
            $this->storeManager->getStore()->getStoreId(),
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );

        $imageHelper = $this->imageHelper->init($quoteItem->getProduct(), 'mini_cart_product_thumbnail');
        $imageData = $this->extensionAttributeFactory->create();
        $imageData->setImageSrc($imageHelper->getUrl())
            ->setImageAlt($imageHelper->getLabel())
            ->setImageWidth($imageHelper->getWidth())
            ->setImageHeight($imageHelper->getHeight());

        $this->appEmulation->stopEnvironmentEmulation();

        return $imageData;
    }
}
