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



namespace Mirasvit\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Repository\ScoreRuleRepository;

abstract class AbstractScoreRule extends Action
{
    protected $scoreRuleRepository;

    protected $resultForwardFactory;

    protected $serializer;

    private   $context;

    private   $registry;

    private   $session;

    public function __construct(
        ScoreRuleRepository $scoreRuleRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->scoreRuleRepository  = $scoreRuleRepository;
        $this->registry             = $registry;
        $this->resultForwardFactory = $resultForwardFactory;

        $this->context    = $context;
        $this->session    = $context->getSession();

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

        $resultPage->getConfig()->getTitle()->prepend((string)__('Score Rules'));

        return $resultPage;
    }

    /**
     * @return ScoreRuleInterface
     */
    protected function initModel()
    {
        $model = $this->scoreRuleRepository->create();

        if ($this->getRequest()->getParam(ScoreRuleInterface::ID)) {
            $model = $this->scoreRuleRepository->get((int) $this->getRequest()->getParam(ScoreRuleInterface::ID));
        }

        $this->registry->register(ScoreRuleInterface::class, $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Search::search_score_rule');
    }
}
