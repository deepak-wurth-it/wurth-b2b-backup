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

namespace Mirasvit\LayeredNavigation\Plugin\Frontend\Catalog\Block\Product\ListProduct;

use Magento\Framework\App\RequestInterface;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\ConfigTrait;

class AjaxCategoryWrapper
{
    use ConfigTrait;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * AjaxCategoryWrapper constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param string                                     $result
     *
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        if (!$this->isAjaxEnabled() && $subject->getNameInLayout() === 'category.products.list') {
            // use for filter opener
            return ConfigProvider::NAV_REPLACER_TAG . $result;
        }

        if (!$this->isAjaxEnabled()
            || $subject->getNameInLayout() !== 'category.products.list'
            || $this->isExternalRequest($this->request)
        ) {
            return $result;
        }

        return ConfigProvider::NAV_REPLACER_TAG . '<div id="' . ConfigProvider::AJAX_PRODUCT_LIST_WRAPPER_ID . '">'
            . $result . '</div>';
    }
}
