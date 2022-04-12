<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Type;

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;

class Store implements TypeInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Store constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_STORE;
    }

    /**
     * @return array|string[]
     */
    public function getAggregators()
    {
        return ['none'];
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return self::VALUE_TYPE_NUMBER;
    }

    /**
     * @return string
     */
    public function getJsType()
    {
        return self::JS_TYPE_SELECT;
    }

    /**
     * @return string
     */
    public function getJsFilterType()
    {
        return self::JS_TYPE_SELECT;
    }

    /**
     * @param number|string       $actualValue
     * @param AggregatorInterface $aggregator
     *
     * @return mixed|number|string
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        $options = $this->getOptions();

        foreach ($options as $option) {
            if ($option['value'] == $actualValue) {
                return $option['label'];
            }
        }

        return self::NA;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options  = [];
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $options[] = [
                'label' => 'Website: ' . $website->getName(),
                'value' => array_keys($website->getStoreIds()),
            ];

            /** @var \Magento\Store\Model\Group $group */
            foreach ($website->getGroups() as $group) {
                /** @var \Magento\Store\Model\Store $store */
                foreach ($group->getStores() as $store) {
                    $options[] = [
                        'label' => '⋅⋅⋅' . $website->getName() . ' › ' . $store->getName(),
                        'value' => $store->getId(),
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @param number|string       $actualValue
     * @param AggregatorInterface $aggregator
     *
     * @return number|string
     */
    public function getPk($actualValue, AggregatorInterface $aggregator)
    {
        return $actualValue;
    }
}
