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

namespace Mirasvit\Brand\Plugin\Frontend\Mirasvit\Seo\Helper\Data;

use Mirasvit\Brand\Service\BrandActionService;

class IgnoreBrandActionPlugin
{
    private $brandActionService;

    public function __construct(
        BrandActionService $brandActionService
    ) {
        $this->brandActionService = $brandActionService;
    }

    /**
     * @param mixed $subject
     * @param bool  $result
     *
     * @return bool
     */
    public function afterIsIgnoredActions($subject, $result)
    {
        return $this->brandActionService->isBrandPage() || $result;
    }
}
