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



namespace Mirasvit\SearchReport\Reports;

use Mirasvit\SearchReport\Api\Data\LogInterface;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;

class Query extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Search Terms');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'search_report_query';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable(LogInterface::TABLE_NAME);
        $this->addFastFilters([
            'mst_search_report_log|created_at',
        ]);

        $this->setDefaultColumns([
            'mst_search_report_log|query',
            'mst_search_report_log|log_id__cnt',
            'mst_search_report_log|users',
            'mst_search_report_log|engagement',
        ]);


        $this->setDefaultDimension('mst_search_report_log|query');

        $this->setDimensions([
            'mst_search_report_log|query',
        ]);


        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_search_report_log|log_id__cnt',
            ]);
    }
}
