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
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $pool;

    public function __construct(
        UpgradeSchema\UpgradeSchema101 $upgrade101,
        UpgradeSchema\UpgradeSchema103 $upgrade103,
        UpgradeSchema\UpgradeSchema105 $upgrade105,
        UpgradeSchema\UpgradeSchema108 $upgrade108,
        UpgradeSchema\UpgradeSchema1010 $upgrade1010,
        UpgradeSchema\UpgradeSchema1012 $upgrade1012
    ) {
        $this->pool = [
            '1.0.1'  => $upgrade101,
            '1.0.3'  => $upgrade103,
            '1.0.5'  => $upgrade105,
            '1.0.8'  => $upgrade108,
            '1.0.10' => $upgrade1010,
            '1.0.12' => $upgrade1012,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
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
