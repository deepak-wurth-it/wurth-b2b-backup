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

namespace Mirasvit\Brand\Plugin\Frontend\Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Service\BrandLogoService;

class AddBrandLogoPlugin
{
    private $isProductListProduct;

    private $brandLogoService;

    private $brandAttribute;

    public function __construct(
        Config $config,
        BrandLogoService $brandLogoService
    ) {
        $this->isProductListProduct = $config->getBrandLogoConfig()->isProductListBrandLogoEnabled();
        $this->brandLogoService     = $brandLogoService;
        $this->brandAttribute       = $config->getGeneralConfig()->getBrandAttribute();
    }

    /**
     * @param ListProduct $subject
     * @param callable    $proceed
     * @param Product     $product
     *
     * @return string
     */
    public function aroundGetProductDetailsHtml(
        ListProduct $subject,
        callable $proceed,
        Product $product
    ) {
        $html = $proceed($product);

        if (!is_object($product) || !$this->isProductListProduct || !$this->brandAttribute) {
            return $html;
        }

        $product->load($product->getId()); // in some cases attribute's data is absent if the model is not loaded
        $optionId = (int)$product->getData($this->brandAttribute);

        if (!$optionId) {
            return $html;
        }

        $this->brandLogoService->setBrandDataByOptionId($optionId);
        $logo = $this->brandLogoService->getLogoHtml();

        return $html . $logo;
    }
}
