<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Model\SysInfo\Provider;

use Amasty\Base\Model\SysInfo\InfoProviderInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;

class Config implements InfoProviderInterface
{
    const CONFIG_PATH_KEY = 'path';
    const CONFIG_VALUE_KEY = 'value';

    /**
     * @var ConfigCollectionFactory
     */
    private $configCollectionFactory;

    public function __construct(
        ConfigCollectionFactory $configCollectionFactory
    ) {
        $this->configCollectionFactory = $configCollectionFactory;
    }

    public function generate(): array
    {
        $configData = [];

        $configCollection = $this->configCollectionFactory->create()
            ->addFieldToSelect([self::CONFIG_PATH_KEY, self::CONFIG_VALUE_KEY]);

        foreach ($this->getPathConditions() as $condition) {
            $configCollection->addFieldToFilter(self::CONFIG_PATH_KEY, $condition);
        }

        foreach ($configCollection->getData() as $config) {
            $path = $this->preparePath($config[self::CONFIG_PATH_KEY]);
            $configData[$path] = $config[self::CONFIG_VALUE_KEY];
        }

        return $configData;
    }

    protected function getPathConditions(): array
    {
        return [
            ['like' => 'am%'],
            ['nlike' => '%token%']
        ];
    }

    private function preparePath(string $path): string
    {
        return str_replace('/', '_', $path);
    }
}
