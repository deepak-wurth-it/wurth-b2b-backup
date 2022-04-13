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


declare(strict_types=1);

namespace Mirasvit\Search\Index\Magento\Catalog\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

class Index extends AbstractIndex
{
    private $eavConfig;

    private $identifier;

    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        string $identifier
    ) {
        $this->eavConfig  = $eavConfig;
        $this->identifier = $identifier;

        parent::__construct($context);
    }

    public function getName(): string
    {
        return 'Magento / Attribute';
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): InstanceInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getType(): string
    {
        return 'magento_catalog_attribute';
    }

    public function getAttributes(): array
    {
        return [
            'label' => __('Attribute value (option)'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'value';
    }

    public function buildSearchCollection(): Collection
    {
        //        $this->setRecentId($this->getIndexId());
        $ids = $this->context->getSearcher()->getMatchedIds();

        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        $attribute = $this->eavConfig->getAttribute(
            'catalog_product',
            $this->getIndex()->getProperty('attribute')
        );

        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                if (in_array($option['value'], $ids)) {
                    $collection->addItem(
                        new DataObject($option)
                    );
                }
            }
        }

        return $collection;
    }

    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        if ($lastEntityId) {
            return $collection->toArray()['items'];
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getIndex()->getProperty('attribute'));

        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $collection->addItem(
                    new DataObject($option)
                );
            }
        }

        return $collection->toArray()['items'];
    }
}
