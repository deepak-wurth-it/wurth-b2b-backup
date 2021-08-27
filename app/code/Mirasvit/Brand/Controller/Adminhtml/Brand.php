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

namespace Mirasvit\Brand\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Model\Brand\PostData\Processor as PostDataProcessor;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Repository\BrandPageRepository;

abstract class Brand extends Action
{
    protected $brandPageRepository;


    protected $config;

    protected $postDataProcessor;

    private   $context;

    public function __construct(
        BrandPageRepository $brandPageRepository,
        Context $context,
        PostDataProcessor $postDataProcessor,
        Config $config
    ) {
        $this->brandPageRepository = $brandPageRepository;
        $this->context             = $context;
        $this->postDataProcessor   = $postDataProcessor;
        $this->config              = $config;

        parent::__construct($context);
    }

    /**
     * @return BrandPageInterface
     */
    public function initModel()
    {
        $model = $this->brandPageRepository->create();

        if ($this->getRequest()->getParam('id')) {
            $model = $this->brandPageRepository->get((int)$this->getRequest()->getParam('id'));
        }

        return $model;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::content');
        $resultPage->getConfig()->getTitle()->prepend((string)__('Brand Pages'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Brand::brand_brand');
    }
}
