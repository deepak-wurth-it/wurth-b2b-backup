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

namespace Mirasvit\Brand\Repository;

use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Api\Data\BrandInterfaceFactory;
use Mirasvit\Brand\Service\BrandAttributeService;

class BrandRepository
{
    private $brandFactory;

    private $brandAttributeService;

    public function __construct(
        BrandAttributeService $brandAttributeService,
        BrandInterfaceFactory $brandFactory
    ) {
        $this->brandFactory          = $brandFactory;
        $this->brandAttributeService = $brandAttributeService;
    }

    public function create(array $data = []): BrandInterface
    {
        return $this->brandFactory->create($data);
    }

    /** @return BrandInterface[] */
    public function getList(): array
    {
        $list = [];

        foreach ($this->brandAttributeService->getVisibleBrandOptions() as $option) {
            $brand  = $this->create(['data' => $option]);
            $list[] = $brand;
        }

        return $list;
    }

    /** @return BrandInterface[] */
    public function getFullList(): array
    {
        $list = [];

        foreach ($this->brandAttributeService->getAllBrandOptions() as $option) {
            $brand  = $this->create(['data' => $option]);
            $list[] = $brand;
        }

        return $list;
    }

    public function get(int $id): ?BrandInterface
    {
        foreach ($this->getFullList() as $brand) {
            if ($brand->getId() == $id) {
                return $brand;
            }
        }

        return null;
    }
}
