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



namespace Mirasvit\Search\Ui\Validator;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Model\ConfigProvider;

class DataProvider extends AbstractDataProvider
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->collection     = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return mixed|void|null
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }

    public function getData(): array
    {
        return [
            'items' => [
                [
                    'id'            => 0,
                    'id_field_name' => 'id',
                    'engine'        => $this->configProvider->getEngine(),
                ],
            ],
        ];
    }
}
