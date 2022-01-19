<?php
declare(strict_types=1);

namespace Amasty\Promo\Setup;

use Amasty\Promo\Setup\Operation\ClearActionsForWholeCart;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ClearActionsForWholeCart
     */
    private $clearActionsForWholeCart;

    public function __construct(
        ClearActionsForWholeCart $clearActionsForWholeCart
    ) {
        $this->clearActionsForWholeCart = $clearActionsForWholeCart;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.10.7', '<')) {
            $this->clearActionsForWholeCart->execute($setup);
        }

        $setup->endSetup();
    }
}
