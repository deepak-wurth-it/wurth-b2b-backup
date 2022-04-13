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



namespace Mirasvit\Search\Block\Index\Mirasvit\Brand;

use Mirasvit\Search\Service\IndexService;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Search\Block\Index\Base;

class Page extends Base
{
    private $storeManager;

    private $objectManager;

    public function __construct(
        IndexService $indexService,
        ObjectManagerInterface $objectManager,
        Context $context
    ) {
        $this->storeManager    = $context->getStoreManager();
        $this->objectManager   = $objectManager;

        parent::__construct($indexService, $objectManager, $context);
    }

    public function getBrandUrl(object $brand) : string
    {
        $brandUrlService = $this->objectManager->create('\Mirasvit\Brand\Service\BrandUrlService');
        return $brandUrlService->getBrandUrl($brand, $this->storeManager->getStore()->getId());
    }
    
}
