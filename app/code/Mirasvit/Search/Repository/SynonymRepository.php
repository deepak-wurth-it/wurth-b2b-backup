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

use Magento\Search\Model\ResourceModel\SynonymGroup\Collection;
use Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory;
use Magento\Search\Model\SynonymGroup;
use Magento\Search\Model\SynonymGroupFactory;

class SynonymRepository
{
    private $factory;

    private $collectionFactory;

    public function __construct(
        SynonymGroupFactory $factory,
        CollectionFactory $collectionFactory
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function get(int $id): ?SynonymGroup
    {
        $synonym = $this->create();
        $synonym->load($id);

        return $synonym->getId() ? $synonym : null;
    }

    public function create(): SynonymGroup
    {
        return $this->factory->create();
    }

    public function delete(SynonymGroup $synonym): SynonymRepository
    {
        $synonym->delete();

        return $this;
    }

    public function save(SynonymGroup $synonym): SynonymGroup
    {
        $synonym->save();

        return $synonym;
    }
}
