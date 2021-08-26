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



namespace Mirasvit\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirasvit\Core\Api\CronHelperInterface;
use Mirasvit\Core\Api\Service\CronServiceInterface;

class Cron extends AbstractHelper implements CronHelperInterface
{
    /**
     * @var CronServiceInterface 
     */
    private $cronService;

    /**
     * Cron constructor
     *
     * @param CronServiceInterface $cronService
     * @param Context $context
     */
    public function __construct(
        CronServiceInterface $cronService,
        Context $context
    ) {
        $this->cronService = $cronService;

        parent::__construct($context);
    }

    /**
     * Method allows to display message about not working cron job in admin panel.
     * Need call at start of adminhtml controller action.
     *
     * @param bool|string $jobCode Cron job code (from crontab.xml).
     * @param bool|string $output By default - return cron error as adminhtml error message, otherwise - as string.
     * @param string $prefix Additional text to cron job error message.
     * @return array
     * @deprecated
     */
    public function checkCronStatus($jobCode, $output = true, $prefix = '')
    {
        return $this->isCronRunning($jobCode) ? [true, ''] : [false, ''];
    }

    /**
     * Check if cron job is exists db table and executed less 6 hours ago
     *
     * @param false|string $jobCode
     * @return bool
     */
    public function isCronRunning($jobCode)
    {
        $jobCodeArray = $jobCode ? [$jobCode] : [];
        return $this->cronService->isCronRunning($jobCodeArray);
    }
}
