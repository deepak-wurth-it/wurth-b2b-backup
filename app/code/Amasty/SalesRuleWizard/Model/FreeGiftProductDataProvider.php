<?php

namespace Amasty\SalesRuleWizard\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class FreeGiftProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * ProductDataProvider constructor.
     *
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array             $addFieldStrategies
     * @param array             $addFilterStrategies
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $this->collection->addFieldToFilter(
            'type_id',
            [
                'in' => [
                    'simple',
                    'configurable',
                    'virtual',
                    'downloadable',
                    'bundle',
                    'giftcard'
                ]
            ]
        );

        $this->collection->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');
    }
}
