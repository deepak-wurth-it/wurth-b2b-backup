<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Wcb\Catalog\Model;

use Magento\Framework\Model\AbstractModel;
use Wcb\Catalog\Api\Data\ExtensionAttributeGetterSetterInterface;

class ExtensionAttributeGetterSetter extends AbstractModel implements ExtensionAttributeGetterSetterInterface
{

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }
    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @param string $value
     * @return \Wcb\Catalog\Api\Data\ExtensionAttributeGetterSetterInterface|ExtensionAttributeGetterSetterInterface
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
