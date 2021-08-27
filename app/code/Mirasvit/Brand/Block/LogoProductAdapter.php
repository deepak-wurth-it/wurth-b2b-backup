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

namespace Mirasvit\Brand\Block;

use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Model\Config\Source\ProductPageBrandLogoDescription;
use Mirasvit\Brand\Service\BrandLogoService;

class LogoProductAdapter extends Logo
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var string
     */
    private $brandAttribute;

    /**
     * @var string
     */
    private $productPageBrandLogoDescription;

    public function __construct(
        Context $context,
        BrandLogoService $brandLogoService,
        Config $config,
        RequestHttp $request,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $brandLogoService, $config, $request, $data);

        $this->registry                        = $registry;
        $this->brandAttribute                  = $config->getGeneralConfig()->getBrandAttribute();
        $this->productPageBrandLogoDescription = $config->getBrandLogoConfig()->getProductPageBrandLogoDescription();

        $this->setBrandDataForLogo();
    }

    /**
     * @return string
     */
    public function getImageWidth()
    {
        return $this->config->getBrandLogoConfig()->getProductPageBrandLogoImageWidth();
    }

    public function getTooltipContent(): string
    {
        return $this->brandLogoService->getLogoTooltipContent(
            $this->config->getBrandLogoConfig()->getProductPageBrandLogoTooltip()
        );
    }


    public function getBrandDescription(): string
    {
        $description = '';
        if ($this->productPageBrandLogoDescription == ProductPageBrandLogoDescription::BRAND_LOGO_DESCRIPTION) {
            $description = $this->brandLogoService->getBrandDescription();
        } elseif ($this->productPageBrandLogoDescription == ProductPageBrandLogoDescription::BRAND_LOGO_SHORT_DESCRIPTION) {
            $description = $this->brandLogoService->getBrandShortDescription();
        }

        return $description;
    }

    public function _toHtml(): string
    {
        if ($this->isProductPage()
            && ($product = $this->registry->registry('current_product'))
            && !$product->getData($this->brandAttribute)) {
            return '';
        }

        return parent::_toHtml();
    }


    private function setBrandDataForLogo(): void
    {
        if ($product = $this->registry->registry('current_product')) {
            $optionId = (int)$product->getData($this->brandAttribute);
            $this->brandLogoService->setBrandDataByOptionId($optionId);
        }
    }
}
