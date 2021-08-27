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
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Brand\Model\Config\Config;
use Mirasvit\Brand\Service\BrandLogoService;

class Logo extends Template
{

    protected $config;

    protected $brandLogoService;

    private   $request;

    public function __construct(
        Context $context,
        BrandLogoService $brandLogoService,
        Config $config,
        RequestHttp $request,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->request          = $request;
        $this->config           = $config;
        $this->brandLogoService = $brandLogoService;
    }

    /**
     * @return string
     */
    public function getLogoImageUrl()
    {
        return $this->brandLogoService->getLogoImageUrl('type-' . $this->getImageWidth());
    }

    /**
     * @return string
     */
    public function getImageWidth()
    {
        return $this->config->getBrandLogoConfig()->getProductListBrandLogoImageWidth();
    }

    /**
     * @return string
     */
    public function getBrandTitle()
    {
        return $this->brandLogoService->getBrandTitle();
    }

    /**
     * @return string
     */
    public function getBrandUrl()
    {
        return $this->brandLogoService->getBrandUrl();
    }

    public function getTooltipContent(): string
    {
        return $this->brandLogoService->getLogoTooltipContent(
            $this->config->getBrandLogoConfig()->getProductListBrandLogoTooltip()
        );
    }

    /**
     * @return string
     */
    public function isProductPage()
    {
        if ($this->request->getFullActionName() === 'catalog_product_view') {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function _toHtml()
    {
        if (($this->isProductPage() && !$this->config->getBrandLogoConfig()->isProductPageBrandLogoEnabled())
            || (!$this->isProductPage() && !$this->config->getBrandLogoConfig()->isProductListBrandLogoEnabled())) {
            return '';
        }

        return parent::_toHtml();
    }
}
