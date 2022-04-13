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



namespace Mirasvit\SearchMysql\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Metrics
{
    /**
     * Available metrics
     */
    private $allowedMetrics = ['count', 'sum', 'min', 'max', 'avg'];

    public function build(RequestBucketInterface $bucket): array
    {
        $selectAggregations = [];
        /** @var \Magento\Framework\Search\Request\Aggregation\Metric[] $metrics */
        $metrics = $bucket->getMetrics();

        foreach ($metrics as $metric) {
            $metricType = $metric->getType();
            if (in_array($metricType, $this->allowedMetrics, true)) {
                if ($bucket->getName() == 'category_bucket') {
                    $selectAggregations[$metricType] = "$metricType(main_table.category_id)";
                } else {
                    $selectAggregations[$metricType] = "$metricType(main_table.value)";
                }
            }
        }

        return $selectAggregations;
    }
}
