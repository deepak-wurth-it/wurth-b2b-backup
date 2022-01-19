<?php

namespace Amasty\Conditions\Plugin\Framework\Api;

use Amasty\Conditions\Plugin\Cart\CartTotalRepository as CartTotalRepositoryPlugin;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\TotalsInterface;

class DataObjectHelperPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * resolve fatal
     *
     * @see CartTotalRepositoryPlugin::beforeGet
     *
     * @param \Magento\Framework\Api\DataObjectHelper $subject
     * @param object $dataObject
     * @param array $data
     * @param string $interfaceName
     *
     * @return array
     */
    public function beforePopulateWithArray(
        \Magento\Framework\Api\DataObjectHelper $subject,
        $dataObject,
        array $data,
        $interfaceName
    ) {
        if (trim($interfaceName, '\\') === TotalsInterface::class
            && $this->registry->registry(CartTotalRepositoryPlugin::REGISTRY_IGNORE_EXTENSION_ATTRIBUTES_KEY)
        ) {
            unset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }

        return [$dataObject, $data, $interfaceName];
    }
}
