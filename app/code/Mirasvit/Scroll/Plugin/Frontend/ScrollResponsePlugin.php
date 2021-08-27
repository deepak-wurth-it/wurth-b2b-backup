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

namespace Mirasvit\Scroll\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\Scroll\Model\ConfigProvider;

/**
 * @see \Magento\Framework\App\ActionInterface::execute()
 */
class ScrollResponsePlugin
{
    const PARAM_IS_SCROLL = 'is_scroll';

    private $configProvider;

    private $request;

    private $response;

    private $layout;

    public function __construct(
        ConfigProvider $config,
        RequestInterface $request,
        ResponseInterface $response,
        LayoutInterface $layout
    ) {
        $this->configProvider = $config;
        $this->request        = $request;
        $this->response       = $response;
        $this->layout         = $layout;
    }

    /**
     * @param object                               $subject
     * @param \Magento\Framework\App\Response\Http $response
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function afterExecute($subject, $response)
    {
        if (!$this->canProcess()) {
            return $response;
        }

        /** @var \Mirasvit\Scroll\Block\Scroll $scrollBlock */
        $scrollBlock = $this->layout->getBlock('product.list.scroll');

        if (!$scrollBlock) {
            return $response;
        }

        $products = $this->getProductListBlock();

        return $this->response->representJson(\Zend_Json::encode([
            'products' => $products ? $products->toHtml() : '',
            'config'   => $scrollBlock->getJsConfig(),
        ]));
    }

    private function canProcess(): bool
    {
        return $this->configProvider->isEnabled()
            && $this->request->isAjax()
            && $this->request->has(self::PARAM_IS_SCROLL);
    }

    private function getProductListBlock(): ?BlockInterface
    {
        if (in_array($this->request->getFullActionName(), ['brand_brand_view', 'all_products_page_index_index'], true)) {
            $block = $this->layout->getBlock('category.products.list');
        } elseif (in_array($this->request->getFullActionName(), ['mpbrand_index_view'])) {
            $block = $this->layout->getBlock('brand.category.products');
        } else {
            $block = $this->layout->getBlock('category.products') ? : $this->layout->getBlock('search.result');
        }

        return $block ? $block : null;
    }
}
