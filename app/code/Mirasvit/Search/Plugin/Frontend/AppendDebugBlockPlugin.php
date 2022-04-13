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



namespace Mirasvit\Search\Plugin\Frontend;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\Search\Service\DebugService;

/**
 * @see \Magento\Framework\Controller\ResultInterface::renderResult()
 */
class AppendDebugBlockPlugin
{
    private $response;

    private $layout;

    private $debugService;

    public function __construct(
        HttpResponse $response,
        LayoutInterface $layout,
        DebugService $debugService
    ) {
        $this->response     = $response;
        $this->layout       = $layout;
        $this->debugService = $debugService;
    }

    public function afterRenderResult(\Magento\Framework\Controller\ResultInterface $subject,object $result): object
    {
        if (!$this->debugService->isEnabled()) {
            return $result;
        }

        if ($debugBlock = $this->layout->createBlock(\Mirasvit\Search\Block\Debug::class)) {
            /** @var \Mirasvit\Search\Block\Debug $debugBlock */
            $this->response->appendBody($debugBlock->toHtml());
        }

        return $result;
    }
}
