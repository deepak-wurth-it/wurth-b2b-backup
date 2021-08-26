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

namespace Mirasvit\Brand\Model\Brand\AttributeCode;

use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ReadHandler constructor.
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $attributeId = (int)$entity->getAttributeId();
        if ($attributeId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool
                    ->getMetadata(BrandPageInterface::class)
                    ->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('eav_attribute'), ['attribute_code'])
                ->where('attribute_id = ?', $attributeId);
            $attributeCode = $connection->fetchOne($select);
            if ($attributeCode) {
                $entity->setAttributeCode($attributeCode);
            }
        }
        return $entity;
    }
}
