<?php

namespace Amasty\Conditions\Setup;

use Amasty\Conditions\Setup\Operation\AddConditionsQuoteTable;
use Amasty\Conditions\Setup\Operation\AddQuoteIdIndex;
use Amasty\Conditions\Setup\Operation\ChangeColumnDefinition;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade Schema scripts
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var AddConditionsQuoteTable
     */
    private $addConditionsQuoteTable;

    /**
     * @var ChangeColumnDefinition
     */
    private $changeColumnDefinition;

    /**
     * @var Operation\AddQuoteIdIndex
     */
    private $addQuoteIdIndex;

    public function __construct(
        AddConditionsQuoteTable $addConditionsQuoteTable,
        ChangeColumnDefinition $changeColumnDefinition,
        AddQuoteIdIndex $addQuoteIdIndex
    ) {
        $this->addConditionsQuoteTable = $addConditionsQuoteTable;
        $this->changeColumnDefinition = $changeColumnDefinition;
        $this->addQuoteIdIndex = $addQuoteIdIndex;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.4.1', '<')) {
            $this->addConditionsQuoteTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.4.2', '<')) {
            $this->changeColumnDefinition->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.5.1', '<')) {
            $this->addQuoteIdIndex->execute($setup);
        }
    }
}
