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

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterfaceFactory;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\Collection;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;

class BrandPageRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    public function __construct(
        BrandPageInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
    }

    public function create(): BrandPageInterface
    {
        return $this->factory->create();
    }

    /** @return Collection|BrandPageInterface[] */
    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function get(int $id): ?BrandPageInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(BrandPageInterface $brandPage): BrandPageInterface
    {
        return $this->entityManager->save($brandPage);
    }

    public function delete(BrandPageInterface $brandPage): void
    {
        $this->entityManager->delete($brandPage);
    }
}
