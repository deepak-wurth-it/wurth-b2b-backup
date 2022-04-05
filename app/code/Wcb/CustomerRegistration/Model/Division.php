<?php
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Model;
use Magento\Framework\Model\AbstractModel;

class Division extends AbstractModel
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Wcb\CustomerRegistration\Model\ResourceModel\Division::class);
    }
}
