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

namespace Mirasvit\Brand\Controller\Brand;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Registry;
use Mirasvit\Brand\Repository\BrandRepository;
use Mirasvit\Brand\Service\BrandPageMetaService;

class View implements HttpGetActionInterface
{
    private $registry;

    private $storeManager;

    private $categoryRepository;

    private $catalogSession;

    private $brandPageMetaService;

    private $brandRepository;

    private $context;

    public function __construct(
        BrandRepository $brandRepository,
        Registry $registry,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Session $catalogSession,
        BrandPageMetaService $brandPageMetaService,
        Context $context
    ) {
        $this->registry             = $registry;
        $this->storeManager         = $storeManager;
        $this->categoryRepository   = $categoryRepository;
        $this->catalogSession       = $catalogSession;
        $this->brandPageMetaService = $brandPageMetaService;
        $this->brandRepository      = $brandRepository;
        $this->context              = $context;
    }

    public function execute()
    {
        $brand = $this->getBrand();

        if (!$brand) {
            /** @var \Magento\Framework\Controller\Result\Forward $forward */
            $forward = $this->context->getResultFactory()
                ->create(ResultFactory::TYPE_FORWARD);

            return $forward->forward('noroute');
        }

        $this->registry->setBrand($brand)
            ->setBrandPage($brand->getPage());

        $this->initCategory();

        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $this->context->getResultFactory()
            ->create(ResultFactory::TYPE_PAGE);

        $page->addPageLayoutHandles(['brand' => $brand->getPage()->getAttributeOptionId()]);

        return $this->brandPageMetaService->apply($page);
    }

    public function initCategory(): ?CategoryInterface
    {
        $categoryId = (int)$this->storeManager->getStore()->getRootCategoryId();

        if (!$categoryId) {
            return null;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
            $category->setData('is_anchor', 1);
        } catch (\Exception $e) {
            return null;
        }

        $this->catalogSession->setLastVisitedCategoryId($category->getId());

        //        if (!$this->registry->registry('current_category')) {
        //            $this->registry->register('current_category', $category);
        //        }

        try {
            $this->context->getEventManager()->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category]
            );
        } catch (\Exception $e) {
            $this->context->getMessageManager()->addExceptionMessage($e);

            return null;
        }

        return $category;
    }

    private function getBrand(): ?BrandInterface
    {
        $brandOptionId = (int)$this->context->getRequest()->getParam('attribute_option_id');

        return $this->brandRepository->get($brandOptionId);
    }
}
