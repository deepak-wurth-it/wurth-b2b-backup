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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Plugin\Frontend\CatalogSearch\Controller\Result\Index;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\LayeredNavigation\Model\Config\ConfigTrait;
use Mirasvit\LayeredNavigation\Service\AjaxResponseService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @see \Magento\CatalogSearch\Controller\Result\Index::execute()
 */
class AjaxSearchPlugin
{
    use ConfigTrait;

    private $response;

    private $redirect;

    private $ajaxResponseService;

    private $resultFactory;

    private $objectManager;

    private $url;

    private $storeManager;

    private $queryFactory;

    private $layerResolver;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        AjaxResponseService $ajaxResponseService
    ) {
        $this->objectManager       = $context->getObjectManager();
        $this->resultFactory       = $context->getResultFactory();
        $this->storeManager        = $storeManager;
        $this->queryFactory        = $queryFactory;
        $this->layerResolver       = $layerResolver;
        $this->ajaxResponseService = $ajaxResponseService;
        $this->response            = $context->getResponse();
        $this->redirect            = $context->getRedirect();
        $this->url                 = $context->getUrl();
    }


    /**
     * @param \Magento\Catalog\Controller\Category\View $subject
     * @param callable                                  $proceed
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function aroundExecute($subject, callable $proceed)
    {
        if (!$this->isAllowed($subject->getRequest())) {
            return $proceed();
        }

        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->queryFactory->get();

        $query->setStoreId($this->storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();

                $redirect = $query->getRedirect();
                if ($redirect && $this->url->getCurrentUrl() !== $redirect) {
                    $this->getResponse()->setRedirect($redirect);

                    return false;
                }
            }

            $this->objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();

            $page = $this->resultFactory->create('page');
            if ($this->isAllowed($subject->getRequest())) {
                return $this->ajaxResponseService->getAjaxResponse($page);
            }

            return $page;
        } else {
            $this->getResponse()->setRedirect($this->redirect->getRedirectUrl());
        }
    }

    /**
     * Retrieve response object
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
