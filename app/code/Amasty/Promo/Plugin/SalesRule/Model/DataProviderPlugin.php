<?php

namespace Amasty\Promo\Plugin\SalesRule\Model;

use Amasty\Promo\Api\Data\GiftRuleInterface;

/**
 * Additional Data for Sales Rule form
 */
class DataProviderPlugin
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(\Amasty\Base\Model\Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Convert Free Gift Rule data to Array
     *
     * @param \Magento\SalesRule\Model\Rule\DataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(\Magento\SalesRule\Model\Rule\DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item[GiftRuleInterface::EXTENSION_ATTRIBUTES_KEY][GiftRuleInterface::EXTENSION_CODE])
                    && $item[GiftRuleInterface::EXTENSION_ATTRIBUTES_KEY][GiftRuleInterface::EXTENSION_CODE] instanceof
                    GiftRuleInterface
                ) {
                    $rule = $item[GiftRuleInterface::EXTENSION_ATTRIBUTES_KEY][GiftRuleInterface::EXTENSION_CODE]
                        ->toArray();

                    if (empty($rule)) {
                        unset($item[GiftRuleInterface::EXTENSION_ATTRIBUTES_KEY][GiftRuleInterface::EXTENSION_CODE]);
                        continue;
                    }

                    $item[GiftRuleInterface::EXTENSION_ATTRIBUTES_KEY][GiftRuleInterface::EXTENSION_CODE] = $rule;

                }
            }
        }

        return $result;
    }
}
