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


namespace Mirasvit\Report\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;
use Mirasvit\Report\Api\Service\EmailServiceInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Email extends Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EmailRepositoryInterface
     */
    protected $emailRepository;

    /**
     * @var EmailServiceInterface
     */
    protected $emailService;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Email constructor.
     * @param Context $context
     * @param Registry $registry
     * @param EmailRepositoryInterface $emailRepository
     * @param EmailServiceInterface $emailService
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EmailRepositoryInterface $emailRepository,
        EmailServiceInterface $emailService,
        ForwardFactory $resultForwardFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->emailRepository = $emailRepository;
        $this->emailService = $emailService;
        $this->session = $context->getSession();
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Report::reports');

        $resultPage->getConfig()->getTitle()->prepend(__('Email Notifications'));

        return $resultPage;
    }

    /**
     * @return EmailInterface
     */
    protected function initModel()
    {
        $model = $this->emailRepository->create();

        if ($this->getRequest()->getParam(EmailInterface::ID)) {
            $model = $this->emailRepository->get($this->getRequest()->getParam(EmailInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Report::email');
    }
}
