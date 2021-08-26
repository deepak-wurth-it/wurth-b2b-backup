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
 * @package   mirasvit/module-core
 * @version   1.2.122
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $upgrade101;

    private $upgrade102;

    private $upgrade103;

    private $upgrade104;

    public function __construct(
        Upgrade101 $upgrade101,
        Upgrade102 $upgrade102,
        Upgrade103 $upgrade103,
        Upgrade104 $upgrade104
    ) {
        $this->upgrade101 = $upgrade101;
        $this->upgrade102 = $upgrade102;
        $this->upgrade103 = $upgrade103;
        $this->upgrade104 = $upgrade104;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->upgrade101->upgrade($installer, $context);
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->upgrade102->upgrade($installer, $context);
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->upgrade103->upgrade($installer, $context);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->upgrade104->upgrade($installer, $context);
        }
    }
}
