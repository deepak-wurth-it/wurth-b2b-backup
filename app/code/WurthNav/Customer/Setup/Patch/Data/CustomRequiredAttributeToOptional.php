<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WurthNav\Customer\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Address;
use \Magento\Eav\Model\Entity\AttributeFactory;

 
class CustomRequiredAttributeToOptional implements DataPatchInterface, PatchRevertableInterface
{
 
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;
 
    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(Customer::ENTITY, 'lastname', 'is_required', 0);
        $customerSetup->updateAttribute('customer_address', 'telephone', 'is_required', 0);
        $customerSetup->updateAttribute('customer_address', 'lastname', 'is_required', 0);


		$this->moduleDataSetup->getConnection()->endSetup();
    }
 
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(Customer::ENTITY, 'lastname', 'is_required', 1);
        $customerSetup->updateAttribute('customer_address', 'telephone', 'is_required', 1);
        $customerSetup->updateAttribute('customer_address', 'lastname', 'is_required', 1);


        $this->moduleDataSetup->getConnection()->endSetup();
    }
 
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
 
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
         
        ];
    }
}
