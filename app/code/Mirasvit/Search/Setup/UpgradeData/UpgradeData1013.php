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



namespace Mirasvit\Search\Setup\UpgradeData;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeData1013 implements UpgradeDataInterface
{
    private $configWriter;

    public function __construct(
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $currentDBName = $setup->getConnection()->fetchRow('SELECT DATABASE() as current_db;')['current_db'];
            $this->configWriter->save('catalog/search/elasticsearch7_index_prefix', $currentDBName);
            $this->configWriter->save('catalog/search/elasticsearch6_index_prefix', $currentDBName);
            $this->configWriter->save('catalog/search/elasticsearch5_index_prefix', $currentDBName);
    }
}
