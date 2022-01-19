<?php

namespace Amasty\BannersLite\Setup\Operations;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ResourceModel\Banner;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\SalesRule\Model\Data\Rule;

class CreateBannerTable
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
            ->newTable($installer->getTable(Banner::TABLE_NAME))
            ->addColumn(
                BannerInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                BannerInterface::SALESRULE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Salesrule Entity Id'
            )
            ->addColumn(
                BannerInterface::BANNER_TYPE,
                Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'Banner Type'
            )
            ->addColumn(
                BannerInterface::BANNER_IMAGE,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Banner Image'
            )
            ->addColumn(
                BannerInterface::BANNER_ALT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Banner Alt'
            )
            ->addColumn(
                BannerInterface::BANNER_HOVER_TEXT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Banner Hover Text'
            )
            ->addColumn(
                BannerInterface::BANNER_LINK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Banner Link'
            )
            ->addIndex(
                $installer->getIdxName(Banner::TABLE_NAME, [BannerInterface::SALESRULE_ID]),
                [BannerInterface::SALESRULE_ID]
            )
            ->addForeignKey(
                $installer->getFkName(
                    Banner::TABLE_NAME,
                    BannerInterface::SALESRULE_ID,
                    'salesrule',
                    Rule::KEY_RULE_ID
                ),
                BannerInterface::SALESRULE_ID,
                $installer->getTable('salesrule'),
                $linkField,
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Promo Banners Lite Banner Data Table');
    }
}
