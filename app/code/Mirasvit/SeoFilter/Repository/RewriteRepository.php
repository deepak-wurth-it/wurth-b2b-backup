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

namespace Mirasvit\SeoFilter\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Api\Data\RewriteInterfaceFactory;
use Mirasvit\SeoFilter\Model\ResourceModel\Rewrite\Collection;
use Mirasvit\SeoFilter\Model\ResourceModel\Rewrite\CollectionFactory;

class RewriteRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    public function __construct(
        RewriteInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
    }

    public function create(): RewriteInterface
    {
        return $this->factory->create();
    }

    /** @return RewriteInterface[]|Collection */
    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function get(int $id): ?RewriteInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(RewriteInterface $model): RewriteInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(RewriteInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
