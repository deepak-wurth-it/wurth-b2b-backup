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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Core\Service\CompatibilityService;

class Recurring implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        if (CompatibilityService::is22()) {
            $this->upgradeSerializeToJson($setup);
        }
    }

    private function upgradeSerializeToJson(SchemaSetupInterface $setup): void
    {
        $om = CompatibilityService::getObjectManager();

        /** @var \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory */
        $fieldDataConverterFactory = $om->create('Magento\Framework\DB\FieldDataConverterFactory');

        /** @var \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory */
        $queryModifierFactory = $om->create('Magento\Framework\DB\Select\QueryModifierFactory');

        $fieldDataConverter = $fieldDataConverterFactory->create('Magento\Framework\DB\DataConverter\SerializedToJson');
        $queryModifier = $queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'searchautocomplete/general/index',
                        'search/advanced/wildcard_exceptions',
                        'search/advanced/replace_words',
                        'search/advanced/not_words',
                        'search/advanced/long_tail_expressions',
                    ],
                ],
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }
}
