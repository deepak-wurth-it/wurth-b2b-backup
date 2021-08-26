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

namespace Mirasvit\SeoFilter\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    const NAME_SEPARATOR_NONE    = '';
    const NAME_SEPARATOR_DASH    = '_';
    const NAME_SEPARATOR_CAPITAL = 'A';

    const URL_FORMAT_OPTIONS      = 'options';
    const URL_FORMAT_ATTR_OPTIONS = 'attr_options';

    const SEPARATOR_FILTER_VALUES = ',';
    const SEPARATOR_FILTERS       = '-';
    const SEPARATOR_DECIMAL       = ':';

    const FILTER_STOCK  = 'mst_stock';
    const FILTER_SALE   = 'mst_on_sale';
    const FILTER_NEW    = 'mst_new_products';
    const FILTER_RATING = 'rating';

    const LABEL_STOCK_IN  = 'instock';
    const LABEL_STOCK_OUT = 'outofstock';

    const LABEL_RATING_1 = 'rating1';
    const LABEL_RATING_2 = 'rating2';
    const LABEL_RATING_3 = 'rating3';
    const LABEL_RATING_4 = 'rating4';
    const LABEL_RATING_5 = 'rating5';

    private $scopeConfig;

    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request     = $request;
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_seo_filter/general/is_enabled', ScopeInterface::SCOPE_STORE);
    }

    public function isApplicable(): bool
    {
        return $this->isEnabled()
            && in_array($this->request->getFullActionName(), [
                'catalog_category_view',
                'all_products_page_index_index',
                'brand_brand_view',
            ]);
    }

    public function getUrlFormat(): string
    {
        $format = (string)$this->scopeConfig->getValue('mst_seo_filter/general/url_format', ScopeInterface::SCOPE_STORE);

        return $format ? $format : self::URL_FORMAT_OPTIONS;
    }

    public function getNameSeparator(): string
    {
        return (string)$this->scopeConfig->getValue('mst_seo_filter/general/name_separator', ScopeInterface::SCOPE_STORE);
    }

    public function getPrefix(): string
    {
        return (string)$this->scopeConfig->getValue('mst_seo_filter/general/prefix', ScopeInterface::SCOPE_STORE);
    }

    public function isMultiselectEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_nav/general/is_multiselect_enabled');
    }
}
