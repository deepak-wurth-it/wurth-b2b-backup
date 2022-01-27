<?php

namespace Amasty\Promo\Plugin\Block\Product;

/**
 * Configurable product for popup
 */
class Configurable
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    public function __construct(
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->productMetadata = $productMetadata;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')
            && $subject->getRequest()->getModuleName() === 'checkout'
        ) {
            $currentProduct = $subject->getProduct();
            $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
            $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
            $result = $this->jsonDecoder->decode($result);
            $format = $this->localeFormat;

            $result['prices']['oldPrice']['amount'] = $format->getNumber($regularPrice->getAmount()->getValue());
            $result['prices']['basePrice']['amount'] = $format->getNumber($finalPrice->getAmount()->getBaseAmount());
            $result['prices']['finalPrice']['amount'] = $format->getNumber($finalPrice->getAmount()->getValue());

            return $this->jsonEncoder->encode($result);
        }

        return $result;
    }
}
