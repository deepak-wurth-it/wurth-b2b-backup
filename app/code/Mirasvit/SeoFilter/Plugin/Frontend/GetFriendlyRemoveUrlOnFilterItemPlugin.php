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

use Magento\Catalog\Model\Layer\Filter\Item;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Service\FriendlyUrlService;
use Mirasvit\SeoFilter\Service\UrlService;

/**
 * @see \Magento\Catalog\Model\Layer\Filter\Item::getRemoveUrl()
 */
class GetFriendlyRemoveUrlOnFilterItemPlugin
{
    private $friendlyUrlService;

    private $urlService;

    private $config;

    public function __construct(
        FriendlyUrlService $friendlyUrlService,
        UrlService $urlService,
        ConfigProvider $config
    ) {
        $this->friendlyUrlService = $friendlyUrlService;
        $this->urlService         = $urlService;
        $this->config             = $config;
    }

    /**
     * Get url for remove item from filter
     *
     * @param Item   $item
     * @param string $url
     *
     * @return string
     */
    public function afterGetRemoveUrl(Item $item, $url)
    {
        if (!$this->config->isApplicable()) {
            return $url;
        }

        $itemFilter = $item->getFilter();

        if (empty($itemFilter)) {
            return $url;
        }
        $value = $item->getData('value');
        if (is_string($value)) {
            $itemValue  = (string)$value;
        } elseif (is_array($value)) {
            $itemValue  = implode("#", $value);
        } else {
            return $url;
        }

        $attributeCode = $itemFilter->getRequestVar();

        if (!$attributeCode || $itemValue === "") {
            return $url;
        }

        $url = $this->friendlyUrlService->getUrl($attributeCode, $itemValue, true);

        return $url;//$this->urlService->addUrlParams($url);
    }
}
