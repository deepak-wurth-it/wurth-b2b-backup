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
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $pool;
    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        WriterInterface $configWriter,
        UpgradeSchema\UpgradeSchema101 $upgrade101,
        UpgradeSchema\UpgradeSchema102 $upgrade102
    ) {
        $this->configWriter = $configWriter;
        $this->pool = [
            '1.0.1' => $upgrade101,
            '1.0.2' => $upgrade102,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);

                if ($version == '1.0.2') {
                    $this->configWriter->save(
                        'brand/brand_slider/WidgetCode',
                        '{{widget type="Mirasvit\Brand\Block\Widget\BrandSlider" template="widget/brand_slider.phtml"}}'
                    );
                }
            }
        }

        $setup->endSetup();
    }
}
