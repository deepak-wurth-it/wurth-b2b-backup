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



namespace Mirasvit\Core\Ui\CronJobs\Listing;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Api\Search\SearchResultInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];
        /** @var Schedule $item */
        foreach ($searchResult->getItems() as $item) {
            $result['items'][] = [
                'schedule_id'  => $item->getId(),
                'job_code'     => $item->getJobCode(),
                'status'       => $item->getStatus(),
                'messages'     => $item->getMessages(),
                'created_at'   => $item->getCreatedAt(),
                'scheduled_at' => $item->getScheduledAt(),
                'executed_at'  => $item->getExecutedAt(),
                'finished_at'  => $item->getFinishedAt(),
            ];
        }

        return $result;
    }
}
