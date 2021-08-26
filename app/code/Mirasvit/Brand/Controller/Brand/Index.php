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

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Service\BrandPageMetaService;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    private   $brandPageMetaService;

    private   $config;

    private   $request;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Request $request,
        Config $config,
        BrandPageMetaService $brandPageMetaService
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->request              = $request;
        $this->config               = $config;
        $this->brandPageMetaService = $brandPageMetaService;

        parent::__construct($context);
    }

    /**
     * Default customer account page
     * @return void|\Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $originalPathInfo = $this->request->getOriginalPathInfo();
        $fullActionName   = $this->request->getFullActionName();
        if ((str_replace('/', '_', ltrim($originalPathInfo, '/')) == $fullActionName)
            || !$this->config->getGeneralConfig()->getBrandAttribute()) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

            return $resultForward
                ->setModule('cms')
                ->setController('noroute')
                ->forward('index');
        }

        $resultPage = $this->resultPageFactory->create();

        return $this->brandPageMetaService->apply($resultPage, true);
    }
}
