<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Plugin\Setup\Model\DeclarationInstaller;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Setup\Model\DeclarationInstaller;

class ApplyPatchesBeforeDeclarativeSchema
{
    /**
     * @var PatchApplier
     */
    private $patchApplier;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string[]
     */
    private $moduleNames;

    public function __construct(
        PatchApplier $patchApplier,
        ResourceConnection $resourceConnection,
        array $moduleNames = []
    ) {
        $this->patchApplier = $patchApplier;
        $this->resourceConnection = $resourceConnection;
        $this->moduleNames = $moduleNames;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param DeclarationInstaller $declarationInstaller
     * @param array $request
     * @return array|null
     * @throws \Magento\Framework\Setup\Exception
     */
    public function beforeInstallSchema(
        DeclarationInstaller $declarationInstaller,
        array $request
    ): ?array {
        $isDryRun = $request[DryRunLogger::INPUT_KEY_DRY_RUN_MODE] ?? true;
        $connection = $this->resourceConnection->getConnection();

        if (!$isDryRun
            && $connection->isTableExists($this->resourceConnection->getTableName(PatchHistory::TABLE_NAME))
        ) {
            foreach ($this->moduleNames as $moduleName) {
                $this->patchApplier->applySchemaPatch($moduleName);
            }
        }

        return null;
    }
}
