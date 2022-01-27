<?php

namespace Amasty\BannersLite\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var Operations\CreateBannerTable
     */
    private $bannerTable;

    /**
     * @var Operations\CreateBannerRuleTable
     */
    private $bannerRuleTable;

    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Amasty\BannersLite\Setup\Operations\CreateBannerTable $bannerTable,
        \Amasty\BannersLite\Setup\Operations\CreateBannerRuleTable $bannerRuleTable
    ) {
        $this->metadataPool = $metadataPool;
        $this->bannerTable = $bannerTable;
        $this->bannerRuleTable = $bannerRuleTable;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();
        $linkField = $this->metadataPool->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();

        /**
         * Create table 'amasty_banners_lite_banner_data'
         */
        $this->bannerTable->execute($installer, $linkField);

        /**
         * Create table 'amasty_banners_lite_rule'
         */
        $this->bannerRuleTable->execute($installer, $linkField);

        $installer->endSetup();
    }
}
