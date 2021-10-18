<?php


namespace Wcb\Bannerslider\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\GroupFactory;

class Customergroup implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
        private $moduleDataSetup;
    private $groupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupFactory $groupFactory

     ) {
        $this->moduleDataSetup = $moduleDataSetup;
         $this->groupFactory = $groupFactory;
    }
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;
        $groupCode = array('Auto(A)','Cargo(C)','Drvo(D)','Građevina(G)','Metal(M)','Industry(I)','Trade(T)','Auto Trade (B)','Others');
        foreach($groupCode as $groups){
            $group = $this->groupFactory->create();
        $group->setCode($groups)->setTaxClassId(3)->save();
        }
        $this->moduleDataSetup->endSetup();
    }
    public function getAliases()    
    {
        return [];
    }
    public static function getDependencies()
    {
        return [];
    }
}
