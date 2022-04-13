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

namespace Mirasvit\SearchLanding\Controller\Page;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Repository\PageRepository;

class View extends \Magento\CatalogSearch\Controller\Result\Index
{
    private $pageRepository;

    private $resultPageFactory;

    private $registry;

    public function __construct(
        PageRepository $pageRepository,
        Registry $registry,
        PageFactory $pageFactory,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        Context $context
    ) {
        $this->registry          = $registry;
        $this->pageRepository    = $pageRepository;
        $this->resultPageFactory = $pageFactory;

        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(PageInterface::ID);

        $page = $this->pageRepository->get($id);

        $this->registry->register('search_landing_page', $page);

        $resultPage = $this->resultPageFactory->create();

        $resultPage->initLayout();
        $resultPage->addHandle('catalogsearch_result_index');
        $resultPage->addHandle('search_landing_page');

        if ($page->getLayoutUpdate()) {
            $resultPage->addUpdate($page->getLayoutUpdate());
        }

        parent::execute();
    }
}
