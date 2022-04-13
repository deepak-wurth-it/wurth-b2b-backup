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



namespace Mirasvit\Search\Model\Index;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

abstract class AbstractIndex extends DataObject implements InstanceInterface
{
    protected $context;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb[][]
     */
    protected $searchCollection = [];

    /**
     * @var IndexInterface
     */
    private $index;

    public function __construct(Context $context)
    {
        $this->context = $context->getInstance();
        $this->context->getIndexer()->setInstance($this);
        $this->context->getSearcher()->setInstance($this);

        parent::__construct();
    }

    public function getType(): string
    {
        return $this->getIdentifier();
    }

    public function getIndex(): ?IndexInterface
    {
        return $this->index;
    }

    public function setIndex(IndexInterface $index): InstanceInterface
    {
        $this->index = $index;

        return $this;
    }

    public function getIndexer(): Indexer
    {
        return $this->context->getIndexer();
    }


    public function __toString(): string
    {
        return (string)__($this->getName());
    }

    /**
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse()
    {
        return $this->context->getSearcher()->getQueryResponse();
    }

    public function getSearchCollection(): Collection
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        /** @var \Magento\Store\Model\App\Emulation $emulation */
        $emulation = $objectManager->create(\Magento\Store\Model\App\Emulation::class);

        $storeId    = $this->getData('store_id') ? $this->getData('store_id') : 0;
        $storeIndex = $this->getIndexName() . '_' . mb_strtolower($this->getData('title')) . '_' . $storeId;

        if (!isset($this->searchCollection[$storeIndex][$storeId])) {
            $isEmulation = false;
            if ($storeId && $storeId != $storeManager->getStore()->getId()
            ) {
                $emulation->startEnvironmentEmulation($storeId);
                $isEmulation = true;
            }

            $this->searchCollection[$storeIndex][$storeId] = $this->buildSearchCollection();

            if ($isEmulation) {
                $this->searchCollection[$storeIndex][$storeId]->getSize();
                // get size before switch to default store
                $emulation->stopEnvironmentEmulation();
            }
        }

        return $this->searchCollection[$storeIndex][$storeId];
    }

    public function getIndexName(): string
    {
        if ($this->getIdentifier() == 'catalogsearch_fulltext') {
            return $this->getIdentifier();
        }

        $identifier = $this->getIdentifier() . '_' . $this->getIndex()->getId();

        return InstanceInterface::INDEX_PREFIX . $identifier;
    }

    public function getAttributeWeights(): array
    {
        if ($this->getIndex()) {
            return $this->getIndex()->getAttributes();
        }

        return $this->getAttributes();
    }

    public function getAttributeId(string $attributeCode): ?int
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }

    public function reindexAll(int $storeId = null): InstanceInterface
    {
        $index = $this->getIndex();

        $this->context->getIndexer()->reindexAll($storeId);

        $index->setStatus(IndexInterface::STATUS_READY);

        $this->context->getIndexRepository()->save($index);

        return $this;
    }

    /**
     * Callback on model save after
     * @return $this
     */
    public function afterModelSave()
    {
        return $this;
    }

    /**
     * Attribute code by id
     *
     * @param int $attributeId
     *
     * @return string
     */
    public function getAttributeCode($attributeId)
    {
        $keys = array_keys($this->getAttributes());

        return isset($keys[$attributeId]) ? $keys[$attributeId] : 'option';
    }
}
