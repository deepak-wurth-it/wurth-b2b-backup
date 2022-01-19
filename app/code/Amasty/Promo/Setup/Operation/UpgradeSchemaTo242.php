<?php

namespace Amasty\Promo\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchemaTo242
{
    /**
     * @var \Amasty\Promo\Model\ImageMigrate
     */
    private $imageMigrate;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    private $consoleOutput;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    public function __construct(
        \Amasty\Promo\Model\ImageMigrate $imageMigrate,
        \Symfony\Component\Console\Output\ConsoleOutput $consoleOutput,
        \Magento\Framework\App\State $appState
    ) {
        $this->imageMigrate = $imageMigrate;
        $this->consoleOutput = $consoleOutput;
        $this->appState = $appState;
    }

    public function execute(SchemaSetupInterface $setup)
    {
        $this->appState->emulateAreaCode(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            [$this->imageMigrate, 'execute']
        );

        $this->removeBannerColumns($setup);

        $this->consoleOutput->writeln('<options=bold;fg=red;bg=white>'
            . 'Amasty Free Gift: Banners data has been migrated successfully. '
            . 'Please, check your banner settings in Free Gift cart price rules.</>');
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function removeBannerColumns(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_ampromo_rule');
        $connection = $setup->getConnection();

        $connection->dropColumn(
            $table,
            'top_banner_image'
        );
        $connection->dropColumn(
            $table,
            'top_banner_alt'
        );
        $connection->dropColumn(
            $table,
            'top_banner_on_hover_text'
        );
        $connection->dropColumn(
            $table,
            'top_banner_link'
        );
        $connection->dropColumn(
            $table,
            'top_banner_description'
        );
        $connection->dropColumn(
            $table,
            'top_banner_link'
        );
        $connection->dropColumn(
            $table,
            'after_product_banner_image'
        );
        $connection->dropColumn(
            $table,
            'after_product_banner_alt'
        );
        $connection->dropColumn(
            $table,
            'after_product_banner_on_hover_text'
        );
        $connection->dropColumn(
            $table,
            'after_product_banner_link'
        );
        $connection->dropColumn(
            $table,
            'after_product_banner_description'
        );
        $connection->dropColumn(
            $table,
            'label_image'
        );
        $connection->dropColumn(
            $table,
            'label_image_alt'
        );
    }
}
