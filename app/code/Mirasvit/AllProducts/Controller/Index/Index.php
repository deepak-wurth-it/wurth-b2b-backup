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

namespace Mirasvit\AllProducts\Controller\Index;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\AllProducts\Service\MetaService;
use Mirasvit\AllProducts\Service\UrlService;
use Mirasvit\AllProducts\Config\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Session
     */
    private $catalogSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;


    private $metaService;

    private $config;


    private $url;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MetaService $metaService,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Session $catalogSession,
        Registry $registry,
        UrlService $url,
        Config $config
    ) {
        $this->resultPageFactory  = $resultPageFactory;
        $this->metaService        = $metaService;
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession     = $catalogSession;
        $this->registry           = $registry;
        $this->objectManager      = $context->getObjectManager();
        $this->eventManager       = $context->getEventManager();
        $this->url                = $url;
        $this->config             = $config;
        parent::__construct($context);
    }

    /**
     * All products page
     * @return void|\Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        if (!$this->registry->registry(UrlService::IS_CORRECT_URL) || ($this->registry->registry(UrlService::IS_CORRECT_URL) && !$this->config->isEnabled())) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

            return $resultForward
                ->setModule('cms')
                ->setController('noroute')
                ->forward('index');
        }

        $this->initCategory();
        $resultPage = $this->resultPageFactory->create();

        return $this->metaService->apply($resultPage);
    }

    public function initCategory()
    {
        $categoryId = $this->storeManager->getStore()->getRootCategoryId();

        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
            $category->setData('is_anchor', 1);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        $this->catalogSession->setLastVisitedCategoryId($category->getId());
        if (!$this->registry->registry('current_category')) {
            $this->registry->register('current_category', $category);
        }

        try {
            $this->eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->objectManager->get('Psr\Log\LoggerInterface')->critical($e);

            return false;
        }

        return $category;
    }
}
