<?php

namespace Amasty\BannersLite\Setup;

use Amasty\BannersLite\Model\ResourceModel\Banner;
use Amasty\BannersLite\Model\ResourceModel\BannerRule;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable(BannerRule::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable(Banner::TABLE_NAME));

        $installer->endSetup();
    }
}
