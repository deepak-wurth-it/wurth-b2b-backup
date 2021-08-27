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

namespace Mirasvit\LayeredNavigation\Service;

use Mirasvit\Core\Service\AbstractValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ValidationService extends AbstractValidator
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ValidationService constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function testUrlSuffixes()
    {
        $productSuffix = $this->scopeConfig->getValue('catalog/seo/product_url_suffix', ScopeInterface::SCOPE_STORE);
        $categorySuffix = $this->scopeConfig->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);

        if (!empty($productSuffix) && !in_array($productSuffix, ['.html','.htm'])) {
            $this->addWarning("Your Product URL Suffix is misconfigured.
                Please check your Catalog -> Search Engine Optimisation tab and correct it");
        }

        if (!empty($categorySuffix) && !in_array($categorySuffix, ['.html','.htm'])) {
            $this->addWarning("Your Category URL Suffix is misconfigured.
                Please check your Catalog -> Search Engine Optimisation tab and correct it");
        }
    }
}
