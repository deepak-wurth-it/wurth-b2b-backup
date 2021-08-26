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

namespace Mirasvit\LayeredNavigation\Plugin;

use Mirasvit\LayeredNavigation\Service\UrlService;

class ResponseRedirectUrlPlugin
{
    /**
     * @var UrlService
     */
    private $urlService;

    /**
     * ResponseRedirectUrlPlugin constructor.
     * @param UrlService $urlService
     */
    public function __construct(
        UrlService $urlService
    ) {
        $this->urlService = $urlService;
    }

    /**
     * @param \Magento\Store\App\Response\Redirect $subject
     * @param string                               $result
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetRedirectUrl($subject, $result)
    {
        $result = $this->urlService->replaceCommaInUrl($result);

        return $result;
    }
}
