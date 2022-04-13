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
 * @package   mirasvit/module-report-api
 * @version   1.0.49
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Entity;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Mirasvit\ReportApi\Api\Config\SelectInterface;

class EavField extends Field
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var int
     */
    protected $entityTypeId;

    /**
     * @var string
     */
    protected $eavTableAlias;

    /**
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * EavField constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductMetadataInterface $productMetadata
     * @param \Mirasvit\ReportApi\Api\Config\TableInterface $table
     * @param string $name
     * @param int $entityTypeId
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata,
        \Mirasvit\ReportApi\Api\Config\TableInterface $table,
        $name,
        $entityTypeId
    ) {
        parent::__construct($table, $name);

        $this->productMetadata = $productMetadata;

        $this->eavTableAlias       = $this->table->getName() . '_' . $this->name;
        $this->attributeRepository = $attributeRepository;
        $this->entityTypeId        = $entityTypeId;
    }

    /**
     * {@inheritdoc}
     */
    public function toDbExpr()
    {
        if ($this->getAttribute()->getBackend()->isStatic()) {
            return $this->table->getName() . '.' . $this->name;
        } else {
            return $this->eavTableAlias . '.value';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function join(SelectInterface $select)
    {
        if ($this->getAttribute()->getBackend()->isStatic()) {
            return $select->joinTable($this->table);
        } else {
            $conditions = [];
            if (
                ($this->productMetadata->getEdition() == 'Enterprise' || $this->productMetadata->getEdition() == 'B2B')
                && (strpos($this->eavTableAlias, 'catalog') !== false || strpos($this->eavTableAlias, 'salesrule') !== false)
            ) {
                $conditions[] = $this->eavTableAlias . '.row_id = ' . $this->table->getName() . '.row_id';
            } else {
                $conditions[] = $this->eavTableAlias . '.entity_id = ' . $this->table->getName() . '.entity_id';
            }
            $conditions[] = $this->eavTableAlias . '.attribute_id = ' . $this->getAttribute()->getAttributeId();

            if ($this->entityTypeId === \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
                || $this->entityTypeId === \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE) {
                $conditions[] = $this->eavTableAlias . '.store_id = 0';
            }

            $select->joinTable($this->table);

            return $select->leftJoin(
                [$this->eavTableAlias => $this->getAttribute()->getBackend()->getTable()],
                implode(' AND ', $conditions),
                []
            );
        }
    }

    /**
     * @return AttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttribute()
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get($this->entityTypeId, $this->name);
        }

        return $this->attribute;
    }
}
