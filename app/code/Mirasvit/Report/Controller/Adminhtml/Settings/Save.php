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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Mirasvit\Report\Api\Service\ColumnManagerInterface;

class Save extends Action
{
    /**
     * @var ColumnManagerInterface
     */
    private $columnManager;

    /**
     * Save constructor.
     * @param ColumnManagerInterface $columnManager
     * @param Action\Context $context
     */
    public function __construct(
        ColumnManagerInterface $columnManager,
        Action\Context $context
    ) {
        $this->columnManager = $columnManager;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $reportId       = $this->getRequest()->getParam('report');
        $columns        = $this->getRequest()->getPostValue('columns');

        if ($columns) {
            if (!$reportId) {
                $this->messageManager->addErrorMessage(__('This report no longer exists.'));

                return $resultRedirect->setPath('reports/report/view');
            }

            try {
                $columns = $this->filterByActive($columns);
                $this->columnManager->setActiveColumns($reportId, $columns);

                $this->messageManager->addSuccessMessage(__('Report columns saved.'));

                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        } else {
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mirasvit_Report::report_settings');
    }

    /**
     * @param array $columns
     * @return array
     */
    private function filterByActive(array $columns = [])
    {
        $active = array_map(function ($key, $isActive) {
            return $isActive ? $key : null;
        }, array_keys($columns), $columns);


        return array_filter($active);
    }
}
