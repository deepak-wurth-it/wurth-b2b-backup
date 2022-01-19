<?php

namespace Amasty\Promo\Setup\Operation;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Fix compatibility with EE row_id
 */
class ChangeFk
{
    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var ExternalFKSetup
     */
    private $externalFKSetup;

    public function __construct(MetadataPool $metadata, ExternalFKSetup $externalFKSetup)
    {
        $this->metadata = $metadata;
        $this->externalFKSetup = $externalFKSetup;
    }

    public function execute(SchemaSetupInterface $setup)
    {
        /** @var AdapterInterface $adapter */
        $adapter = $setup->getConnection();
        $amruleTableName = $setup->getTable('amasty_ampromo_rule');
        $salesruleTableName = $setup->getTable('salesrule');
        $foreignKeys = $adapter->getForeignKeys($amruleTableName);
        $linkField = $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();

        foreach ($foreignKeys as $key) {
            if ($key['COLUMN_NAME'] == GiftRuleInterface::SALESRULE_ID && $key['REF_COLUMN_NAME'] != $linkField) {
                $this->setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName);
                $adapter->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
                $this->externalFKSetup->install(
                    $setup,
                    $salesruleTableName,
                    $linkField,
                    $amruleTableName,
                    GiftRuleInterface::SALESRULE_ID
                );
            }
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $amruleTableName
     * @param string $salesruleTableName
     */
    private function setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName)
    {
        $select = $adapter->select()
            ->from(
                $amruleTableName,
                [
                    GiftRuleInterface::SKU,
                    GiftRuleInterface::TYPE,
                    GiftRuleInterface::ITEMS_DISCOUNT,
                    GiftRuleInterface::MINIMAL_ITEMS_PRICE,
                    GiftRuleInterface::APPLY_TAX,
                    GiftRuleInterface::APPLY_SHIPPING
                ]
            )->joinInner(
                ['salesrule' => $salesruleTableName],
                'salesrule.rule_id = ' . $amruleTableName . '.salesrule_id',
                ['salesrule_id' => 'salesrule.row_id']
            )->setPart('disable_staging_preview', true);

        $amRules = $adapter->fetchAll($select);

        if (!empty($amRules)) {
            $adapter->truncateTable($amruleTableName);
            $adapter->insertMultiple($amruleTableName, $amRules);
        }
    }
}
