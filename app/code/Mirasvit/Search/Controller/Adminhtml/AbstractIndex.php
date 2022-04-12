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


declare(strict_types=1);

namespace Mirasvit\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;

abstract class AbstractIndex extends Action
{
    protected $indexRepository;

    protected $resultForwardFactory;

    private   $context;

    private   $session;

    public function __construct(
        Context $context,
        IndexRepository $scoreRuleRepository,
        ForwardFactory $resultForwardFactory
    ) {
        $this->context              = $context;
        $this->indexRepository      = $scoreRuleRepository;
        $this->session              = $context->getSession();
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Search::search');

        $resultPage->getConfig()->getTitle()->prepend((string)__('Search Indexes'));

        return $resultPage;
    }

    protected function initModel(): IndexInterface
    {
        $model = $this->indexRepository->create();

        if ($this->getRequest()->getParam(IndexInterface::ID)) {
            $model = $this->indexRepository->get((int)$this->getRequest()->getParam(IndexInterface::ID));
        }

        return $model;
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()
            ->isAllowed('Mirasvit_Search::search_index');
    }
}
