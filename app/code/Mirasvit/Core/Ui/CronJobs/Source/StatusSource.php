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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Ui\CronJobs\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Cron\Model\Schedule;

class StatusSource implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Error'),
                'value' => Schedule::STATUS_ERROR,
            ],
            [
                'label' => __('Missed'),
                'value' => Schedule::STATUS_MISSED,
            ],
            [
                'label' => __('Running'),
                'value' => Schedule::STATUS_RUNNING,
            ],
            [
                'label' => __('Pending'),
                'value' => Schedule::STATUS_PENDING,
            ],
            [
                'label' => __('Success'),
                'value' => Schedule::STATUS_SUCCESS,
            ],
        ];
    }
}
