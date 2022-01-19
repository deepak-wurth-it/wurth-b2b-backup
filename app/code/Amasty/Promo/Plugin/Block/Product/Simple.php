<?php

namespace Amasty\Promo\Plugin\Block\Product;

class Simple
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->localeFormat = $localeFormat;
        $this->eventManager = $eventManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param \Closure $proceed
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetJsonConfig(
        \Magento\Catalog\Block\Product\View $subject,
        \Closure $proceed
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')
            && ($subject->getRequest()->getModuleName() === 'checkout'
                || $subject->getRequest()->getModuleName() === 'amasty_promo')
        ) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $subject->getProduct();

            $tierPrices = [];
            $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = $tierPrice['price']->getValue();
            }
            $config = [
                'productId' => $product->getId(),
                'priceFormat' => $this->localeFormat->getPriceFormat(),
                'prices' => [
                    'oldPrice' => [
                        'amount' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ],
                    'basePrice' => [
                        'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                        'adjustments' => []
                    ],
                    'finalPrice' => [
                        'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                        'adjustments' => []
                    ]
                ],
                'idSuffix' => '_clone',
                'tierPrices' => $tierPrices
            ];

            $responseObject = new \Magento\Framework\DataObject();
            $this->eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
            if (is_array($responseObject->getAdditionalOptions())) {
                foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                    $config[$option] = $value;
                }
            }

            return $this->jsonEncoder->encode($config);
        }

        return $proceed();
    }
}
