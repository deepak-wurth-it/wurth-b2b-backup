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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreRepository;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Service\StoreResolver;

class Money extends Number implements TypeInterface
{
    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * Money constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreResolver          $storeResolver
     * @param StoreRepository        $storeRepository
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreResolver $storeResolver,
        StoreRepository $storeRepository
    ) {
        $this->priceCurrency   = $priceCurrency;
        $this->storeResolver   = $storeResolver;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_MONEY;
    }

    /**
     * @return array|string[]
     */
    public function getAggregators()
    {
        return ['none', 'sum', 'avg'];
    }

    /**
     * @return string
     */
    public function getJsType()
    {
        return self::JS_TYPE_MONEY;
    }

    /**
     * @param string|float|null   $actualValue
     * @param AggregatorInterface $aggregator
     *
     * @return string
     */
    public function getFormattedValue($actualValue, AggregatorInterface $aggregator)
    {
        $storeId = $this->storeResolver->getStoreId() ?: 0;
        $storeId = !is_int($storeId) && is_array($storeId) ? $storeId[0] : $storeId;
        $store = $this->storeRepository->getById((int)$storeId);

        return $this->priceCurrency->convertAndFormat(
            $actualValue,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $store,
            $store->getDefaultCurrencyCode()
        );
    }
}
