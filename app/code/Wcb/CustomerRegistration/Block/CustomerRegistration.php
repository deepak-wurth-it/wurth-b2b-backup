<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Block\Account;

class CustomerRegistration extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        array $data = []
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        parent::__construct($context, $data);
    }
 
    /**
     * Retrieve customer group collection
     *
     * @return GroupCollection
     */
    public function getCustomerGroupCollection()
    {
        if (!$this->hasData('customer_group_collection')) {
            $collection = $this->groupCollectionFactory->create();
            $this->setData('customer_group_collection', $collection);
        }
 
        return $this->getData('customer_group_collection');
    
}

}