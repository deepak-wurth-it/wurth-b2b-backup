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



namespace Mirasvit\Search\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $pool;

    public function __construct(
        UpgradeData\UpgradeData103 $upgrade103,
        UpgradeData\UpgradeData104 $upgrade104,
        UpgradeData\UpgradeData107 $upgrade107,
        UpgradeData\UpgradeData1011 $upgrade1011,
        UpgradeData\UpgradeData1013 $upgrade1013
    ) {
        $this->pool = [
            '1.0.3'  => $upgrade103,
            '1.0.4'  => $upgrade104,
            '1.0.7'  => $upgrade107,
            '1.0.11' => $upgrade1011,
            '1.0.13' => $upgrade1013,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);
            }
        }
        $setup->endSetup();
    }
}
