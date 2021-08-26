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




namespace Mirasvit\Core\Plugin\Backend\Framework\App\FrontController;


use Magento\Framework\App\RequestInterface;
use Mirasvit\Core\Api\Service\CronServiceInterface;

class CronCheckerPlugin
{
    /** @var \Mirasvit\Core\Service\CronService */
    private $cronService;

    /**
     * CronCheckerPlugin constructor.
     * @param CronServiceInterface $cronService
     */
    public function __construct(
        CronServiceInterface $cronService
    ){
        $this->cronService = $cronService;
    }

    /**
     * @param mixed $subject
     * @param RequestInterface $request
     */
    public function beforeDispatch($subject, RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $moduleName = $request->getControllerModule();

        if (strpos($moduleName, 'Mirasvit_') !== false && $this->shouldDisplayStatus($request)) {
            $this->cronService->outputCronStatus($moduleName);
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function shouldDisplayStatus(RequestInterface $request)
    {
        $isActionAllowed = in_array($request->getActionName(), ['view', 'index']);

        return !$request->isAjax() && !$request->isPost() && $isActionAllowed;
    }
}