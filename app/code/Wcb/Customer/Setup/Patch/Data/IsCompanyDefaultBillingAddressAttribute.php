<?php

namespace Wcb\Customer\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class IsCompanyDefaultBillingAddressAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param Attribute $attribute
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        Attribute $attribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attribute = $attribute;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute('customer_address', 'is_company_main_address', [
            'type' => 'int',
            'label' => 'Is Company Main Address',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'system' => false,
            'group' => 'General',
            'global' => true,
            'position' => 334,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'is_company_main_address');

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'adminhtml_checkout',
            'adminhtml_customer_address'
        ]);

        $this->attribute->save($attribute);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
