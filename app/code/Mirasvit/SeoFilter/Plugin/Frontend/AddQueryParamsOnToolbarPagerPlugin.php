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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Plugin\Frontend;

use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Service\UrlService;

/**
 * @see \Magento\Catalog\Block\Product\ProductList\Toolbar::getPagerUrl()
 */
class AddQueryParamsOnToolbarPagerPlugin
{
    private $urlService;

    private $config;

    public function __construct(
        UrlService $urlHelper,
        ConfigProvider $config
    ) {
        $this->urlService = $urlHelper;
        $this->config     = $config;
    }

    /**
     * @param object $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetPagerUrl($subject, $result)
    {
        if ($this->config->isApplicable()) {
            return $this->urlService->getQueryParams($result);
        }

        return $result;
    }
}
