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

namespace Mirasvit\Brand;

use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;

class Registry
{
    private $brand;

    private $brandPage;

    public function getBrand(): ?BrandInterface
    {
        return $this->brand;
    }

    public function setBrand(BrandInterface $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getBrandPage(): ?BrandPageInterface
    {
        return $this->brandPage;
    }

    public function setBrandPage(BrandPageInterface $brandPage): self
    {
        $this->brandPage = $brandPage;

        return $this;
    }
}
