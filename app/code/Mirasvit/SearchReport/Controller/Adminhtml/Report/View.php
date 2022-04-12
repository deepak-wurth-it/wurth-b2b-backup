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



namespace Mirasvit\SearchReport\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;

class View extends Action
{
    protected $repository;

    protected $registry;

    protected $context;

    public function __construct(
        ReportRepositoryInterface $repository,
        Registry $registry,
        Context $context
    ) {
        $this->repository = $repository;
        $this->registry   = $registry;
        $this->context    = $context;

        parent::__construct($context);
    }

    public function execute(): ResultPage
    {
        $report = $this->getRequest()->getParam('report');
        if (!$report) {
            $report = 'search_report_volume';
        }

        $this->registry->register('current_report', $this->repository->get($report));
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);

        return $resultPage;
    }

    protected function initPage(ResultPage $resultPage): ResultPage
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');
        $resultPage->getConfig()->getTitle()->prepend((string)__('Search'));
        $resultPage->getConfig()->getTitle()->prepend((string)__('Reports'));

        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_SearchReport::search_report');
    }
}
