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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\LayeredNavigation\Block\Navigation\State;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Service\FriendlyUrlService;

/**
 * @see \Magento\LayeredNavigation\Block\Navigation\State::getClearUrl()
 */
class GetClearUrlPlugin
{
    private $categoryRepository;

    private $config;

    private $context;

    private $friendlyUrlService;

    public function __construct(
        FriendlyUrlService $friendlyUrlService,
        CategoryRepositoryInterface $categoryRepository,
        ConfigProvider $config,
        Context $context
    ) {
        $this->friendlyUrlService = $friendlyUrlService;
        $this->categoryRepository = $categoryRepository;
        $this->config             = $config;
        $this->context            = $context;
    }

    /**
     * @param State  $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetClearUrl($subject, $result)
    {
        if (!$this->config->isApplicable()) {
            return $result;
        }

        return $this->friendlyUrlService->getClearUrl();
    }
}
