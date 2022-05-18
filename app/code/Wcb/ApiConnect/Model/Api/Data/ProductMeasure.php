<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Wcb\ApiConnect\Model\Api\Data;

use Magento\Framework\Model\AbstractModel;
use Wcb\ApiConnect\Api\Data\ProductMeasureInterface;

class ProductMeasure extends AbstractModel implements ProductMeasureInterface
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
     * @return \Wcb\ApiConnect\Api\Data\ProductMeasure|ProductMeasure
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
