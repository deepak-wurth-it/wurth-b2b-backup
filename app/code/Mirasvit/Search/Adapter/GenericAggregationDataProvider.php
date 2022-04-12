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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Search\Adapter;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Aggregations data provider stub.
 * Most indexes don't support aggregations, but still provider has to be registered.
 */
class GenericAggregationDataProvider implements DataProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRange()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        return [];
    }
}
