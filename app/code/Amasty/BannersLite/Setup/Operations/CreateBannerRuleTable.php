<?php

namespace Amasty\BannersLite\Setup\Operations;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\BannerRule;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\SalesRule\Model\Data\Rule;

class CreateBannerRuleTable
{
    /**
     * @param SchemaSetupInterface $installer
     * @param string $linkField
     */
    public function execute(SchemaSetupInterface $installer, $linkField)
    {
        $installer->getConnection()->createTable(
            $this->createTable($installer, $linkField)
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param string $linkField
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $installer, $linkField)
    {
        return $installer->getConnection()
            ->newTable($installer->getTable(BannerRule::TABLE_NAME))
            ->addColumn(
                BannerRuleInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                BannerRuleInterface::SALESRULE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Salesrule Entity Id'
            )
            ->addColumn(
                BannerRuleInterface::BANNER_PRODUCT_SKU,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Banner Type'
            )
            ->addColumn(
                BannerRuleInterface::BANNER_PRODUCT_CATEGORIES,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Banner Image'
            )
            ->addColumn(
                BannerRuleInterface::SHOW_BANNER_FOR,
                Table::TYPE_INTEGER,
                1,
                ['nullable' => true],
                'Banner Alt'
            )
            ->addIndex(
                $installer->getIdxName(BannerRule::TABLE_NAME, [BannerRuleInterface::SALESRULE_ID]),
                [BannerRuleInterface::SALESRULE_ID]
            )
            ->addForeignKey(
                $installer->getFkName(
                    BannerRule::TABLE_NAME,
                    BannerRuleInterface::SALESRULE_ID,
                    'salesrule',
                    Rule::KEY_RULE_ID
                ),
                BannerRuleInterface::SALESRULE_ID,
                $installer->getTable('salesrule'),
                $linkField,
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Promo Banners Lite Rule');
    }
}
