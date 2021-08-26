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

namespace Mirasvit\Brand\Service;

use Magento\Framework\App\RequestInterface;

class BrandActionService
{
    const SEPARATOR          = '_';
    const BRAND_INDEX_ACTION = 'brand_index_index';
    const BRAND_VIEW_ACTION  = 'brand_brand_view';
    const BRAND_FULL_ACTION  = ['brand/brand/view/', 'brand/brand/view'];

    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function isBrandViewPage(): bool
    {
        return $this->getFullActionName() === self::BRAND_VIEW_ACTION;
    }

    public function isBrandPage(): bool
    {
        return $this->isBrandIndexPage() || $this->isBrandViewPage();
    }

    private function isBrandIndexPage(): bool
    {
        return $this->getFullActionName() === self::BRAND_INDEX_ACTION;
    }

    private function getFullActionName(): string
    {
        return (string)$this->request->getFullActionName();
    }
}
