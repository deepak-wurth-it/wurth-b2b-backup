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


namespace Mirasvit\SeoNavigation\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\SeoNavigation\Service\MetaServiceInterface;

class AddRobots implements ObserverInterface
{
    /**
     * @var MetaServiceInterface
     */
    private $metaService;

    public function __construct(MetaServiceInterface $metaService)
    {
        $this->metaService = $metaService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var RequestInterface|Http $request */
        $request = $observer->getEvent()->getData('request');

        if (!$request->isAjax()) {
            $this->metaService->apply($request);
        }
    }
}
