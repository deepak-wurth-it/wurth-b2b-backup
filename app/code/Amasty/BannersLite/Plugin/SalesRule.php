<?php

namespace Amasty\BannersLite\Plugin;

use Amasty\BannersLite\Api\Data\BannerInterface;

class SalesRule
{
    /**
     * @var \Amasty\BannersLite\Model\BannerFactory
     */
    private $bannerFactory;

    public function __construct(
        \Amasty\BannersLite\Model\BannerFactory $bannerFactory
    ) {
        $this->bannerFactory = $bannerFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Magento\SalesRule\Model\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadPost(\Magento\SalesRule\Model\Rule $subject, \Magento\SalesRule\Model\Rule $salesRule)
    {
        /** @var array $attributes */
        $attributes = $salesRule->getExtensionAttributes() ?: [];

        if (!isset($attributes[BannerInterface::EXTENSION_CODE])) {
            return $salesRule;
        }

        $extAttributes = $this->getExtAttributes($attributes[BannerInterface::EXTENSION_CODE]);
        $salesRule->setExtensionAttributes($extAttributes);

        return $salesRule;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    private function getExtAttributes($attributes)
    {
        $extAttributes[BannerInterface::EXTENSION_CODE] = [];

        foreach (BannerInterface::BANNER_POSITIONS as $key => $field) {
            /** @var \Amasty\BannersLite\Model\Banner $banner */
            $banner = $this->bannerFactory->create();
            $banner->addData($attributes[$key]);
            $banner->setBannerType($key);

            $extAttributes[BannerInterface::EXTENSION_CODE][] = $banner;
        }

        return $extAttributes;
    }
}
