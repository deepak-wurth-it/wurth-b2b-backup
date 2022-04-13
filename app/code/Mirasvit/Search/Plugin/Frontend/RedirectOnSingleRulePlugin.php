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

namespace Mirasvit\Search\Plugin\Frontend;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\ResponseInterface;
use Mirasvit\Search\Model\ConfigProvider;

/**
 * @see \Mirasvit\Search\Block\Result::toHtml()
 */
class RedirectOnSingleRulePlugin
{
    private $config;

    private $layerResolver;

    private $response;

    public function __construct(
        ConfigProvider $config,
        LayerResolver $layerResolver,
        ResponseInterface $response
    ) {
        $this->config        = $config;
        $this->layerResolver = $layerResolver;
        $this->response      = $response;
    }

    public function afterToHtml(object $block, string $html): string
    {
        if (!$this->config->isRedirectOnSingleResult()) {
            return $html;
        }

        if ($this->layerResolver->get()->getProductCollection()->getSize() == 1) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->layerResolver->get()->getProductCollection()->getFirstItem();

            $this->response
                ->setRedirect($product->getProductUrl())
                ->setStatusCode(301)
                ->sendResponse();
        }

        return $html;
    }
}
