<?php

namespace Amasty\BannersLite\Plugin\SalesRule\Model;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\SalesRule\Model\Data\Rule;

class DataProviderPlugin
{
    /**
     * Needed to compatibility with old version Amasty Rules
     *
     * @var array
     */
    private $emptyArray = [
        BannerInterface::BANNER_ALT => "",
        BannerInterface::BANNER_HOVER_TEXT => ""
    ];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(Serializer $serializer, ImageProcessor $imageProcessor)
    {
        $this->serializer = $serializer;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Convert Promo Banners data to Array
     *
     * @param \Magento\SalesRule\Model\Rule\DataProvider $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function afterGetData(\Magento\SalesRule\Model\Rule\DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item[BannerInterface::EXTENSION_ATTRIBUTES_KEY][BannerInterface::EXTENSION_CODE])) {
                    $ruleId = isset($item[Rule::KEY_RULE_ID]) ? $item[Rule::KEY_RULE_ID] : null;
                    $banners = &$item[BannerInterface::EXTENSION_ATTRIBUTES_KEY][BannerInterface::EXTENSION_CODE];
                    foreach ($banners as $key => $banner) {
                        /** @var \Amasty\BannersLite\Model\Banner $banner */
                        if ($banner instanceof BannerInterface) {
                            $banners[$key] = $this->convertBannerToArray($banner, $ruleId, $key);
                        }
                    }
                }
            }
        }

        return $result;
    }
    //@codingStandardsIgnoreEnd

    /**
     * @param \Amasty\BannersLite\Model\Banner $banner
     * @param int|null $ruleId
     * @param int $bannerPosition
     *
     * @return array
     */
    private function convertBannerToArray(\Amasty\BannersLite\Model\Banner $banner, $ruleId, $bannerPosition)
    {
        $array = $banner->toArray();

        if ($this->isBannerImage($array)) {
            $array[BannerInterface::BANNER_IMAGE]
                = $this->serializer->unserialize($array[BannerInterface::BANNER_IMAGE]);
            $array[BannerInterface::BANNER_IMAGE][0]['url']
                = $this->imageProcessor->getBannerImageUrl($array[BannerInterface::BANNER_IMAGE][0]['name']);
        }

        if (empty($array) && $ruleId) {
            $array += [BannerInterface::BANNER_TYPE => $bannerPosition, BannerInterface::SALESRULE_ID => $ruleId];
            $array = array_merge($array, $this->emptyArray);
        }

        return $array;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function isBannerImage(array $array)
    {
        return isset($array[BannerInterface::BANNER_IMAGE])
            && $array[BannerInterface::BANNER_IMAGE]
            && is_string($array[BannerInterface::BANNER_IMAGE]);
    }
}
