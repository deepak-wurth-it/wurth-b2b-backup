<?php

namespace Wcb\Store\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('wcb_store_pickup'),
                'region_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Region Id'
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('wcb_store_pickup'),
                'region',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'region',
                    'length' => 255
                ]
            );


        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('wcb_store_pickup'),
                'postcode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post Code'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('wcb_store_pickup'),
                'country_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'country  id'
                ]
            );
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
                $installer->getConnection()->addColumn(
                      $installer->getTable('wcb_store_pickup'),
                      'map_url',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                          'nullable' => true,
                          'comment' => 'Map Url'
                      ]
                  );
        }
        if (version_compare($context->getVersion(), '0.0.7', '<')) {
                $installer->getConnection()->addColumn(
                      $installer->getTable('wcb_store_pickup'),
                      'contact_name',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                          'nullable' => true,
                          'comment' => 'contact name '
                      ]
                  );
                  $installer->getConnection()->addColumn(
                        $installer->getTable('wcb_store_pickup'),
                        'contact_email',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'comment' => 'contact email'
                        ]
                    );
                  $installer->getConnection()->addColumn(
                        $installer->getTable('wcb_store_pickup'),
                        'phone',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'comment' => 'Phone'
                        ]
                    );
                    $installer->getConnection()->addColumn(
                          $installer->getTable('wcb_store_pickup'),
                          'fax',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                              'nullable' => true,
                              'comment' => 'fax'
                          ]
                      );
        }
        $installer->endSetup();
    }
}
