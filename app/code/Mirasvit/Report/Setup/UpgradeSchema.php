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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Mirasvit\Report\Api\Data\EmailInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var array
     */
    private $pool;

    /**
     * UpgradeSchema constructor.
     * @param UpgradeSchema\UpgradeSchema101 $upgrade101
     * @param UpgradeSchema\UpgradeSchema102 $upgrade102
     * @param UpgradeSchema\UpgradeSchema103 $upgrade103
     */
    public function __construct(
        UpgradeSchema\UpgradeSchema101 $upgrade101,
        UpgradeSchema\UpgradeSchema102 $upgrade102,
        UpgradeSchema\UpgradeSchema103 $upgrade103
    ) {
        $this->pool = [
            '1.0.1'  => $upgrade101,
            '1.0.2'  => $upgrade102,
            '1.0.3'  => $upgrade103
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
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
