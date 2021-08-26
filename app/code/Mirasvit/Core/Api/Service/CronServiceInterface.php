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




namespace Mirasvit\Core\Api\Service;


interface CronServiceInterface
{
    /**
     * Check if cron job is exists db table and executed less 6 hours ago
     * 
     * @param array $jobCodes
     * @return bool
     */
    public function isCronRunning(array $jobCodes = []);
}