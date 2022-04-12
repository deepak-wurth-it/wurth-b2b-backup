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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Data\IndexInterfaceFactory;
use Mirasvit\Search\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;

class IndexRepository
{
    private static $multiIndexes = ['magento_catalog_attribute'];

    private static $indexCache    = [];

    private static $instanceCache = [];

    private        $entityManager;

    private        $indexFactory;

    private        $indexCollectionFactory;

    private        $objectManager;

    private        $indexPool;

    public function __construct(
        EntityManager $entityManager,
        IndexInterfaceFactory $indexFactory,
        IndexCollectionFactory $indexCollectionFactory,
        ObjectManagerInterface $objectManager,
        array $indexes = []
    ) {
        $this->entityManager          = $entityManager;
        $this->indexFactory           = $indexFactory;
        $this->indexCollectionFactory = $indexCollectionFactory;
        $this->objectManager          = $objectManager;
        $this->indexPool              = $indexes;
    }

    /**
     * @return \Mirasvit\Search\Model\ResourceModel\Index\Collection|IndexInterface[]
     */
    public function getCollection()
    {
        return $this->indexCollectionFactory->create();
    }

    public function delete(IndexInterface $index): IndexRepository
    {
        $this->entityManager->delete($index);

        return $this;
    }

    public function save(IndexInterface $index): IndexInterface
    {
        $this->entityManager->save($index);

        return $index;
    }

    public function get(int $id): ?IndexInterface
    {
        if (array_key_exists($id, self::$indexCache)) {
            return self::$indexCache[$id];
        }

        $index = $this->create();
        $index = $this->entityManager->load($index, $id);

        if (!$index->getId()) {
            return null;
        }

        self::$indexCache[$id] = $index;

        return $index;
    }

    public function getByIdentifier(string $identifier): ?IndexInterface
    {
        if (array_key_exists($identifier, self::$indexCache)) {
            return self::$indexCache[$identifier];
        }

        $index = $this->create()->load($identifier, IndexInterface::IDENTIFIER);

        if (!$index->getId()) {
            return null;
        }

        self::$indexCache[$identifier] = $index;

        return $index;
    }

    public function create(): IndexInterface
    {
        return $this->indexFactory->create();
    }

    /**
     * @return InstanceInterface[]
     */
    public function getList(): array
    {
        $result = [];

        foreach ($this->indexPool as $identifier => $class) {
            $result[] = $this->objectManager->create($class, ['identifier' => $identifier]);
        }

        return $result;
    }

    public function getInstance(IndexInterface $index): ?InstanceInterface
    {
        $identifier = $index->getIdentifier();

        $instance = $this->mapInstanceByIdentifier($identifier);

        $instance
            ->setIndex($index)
            ->setData($index->getData());

        return $instance;
    }

    public function getInstanceByIdentifier(string $identifier): ?InstanceInterface
    {
        $identifier = str_replace(InstanceInterface::INDEX_PREFIX, '', $identifier);

        $instance = $this->mapInstanceByIdentifier($identifier);

        $index = $this->getByIdentifier($identifier);

        $instance
            ->setIndex($index)
            ->setData($index->getData());

        return $instance;
    }

    private function mapInstanceByIdentifier(string $identifier): ?InstanceInterface
    {
        if (!array_key_exists($identifier, self::$instanceCache)) {
            self::$instanceCache[$identifier] = null;

            foreach ($this->getList() as $instance) {
                if ($instance->getIdentifier() == $identifier) {
                    self::$instanceCache[$identifier] = $instance;
                }
            }
        }

        return self::$instanceCache[$identifier];
    }
}
