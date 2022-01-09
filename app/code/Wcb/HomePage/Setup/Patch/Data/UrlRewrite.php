<?php
namespace Wcb\HomePage\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UrlRewrite implements DataPatchInterface
{
   /** @var ModuleDataSetupInterface */
   private $moduleDataSetup;

   /** @var EavSetupFactory */
   private $eavSetupFactory;

   /**
    * @param ModuleDataSetupInterface $moduleDataSetup
    * @param EavSetupFactory $eavSetupFactory
    */
   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
       \Magento\Store\Model\StoreManagerInterface $storeManager
   ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_urlRewriteFactory = $urlRewriteFactory;
        $this->storeManager = $storeManager;
   }

   /**
    * {@inheritdoc}
    */
   public function apply()
   {
        //get Store Url without index.php
        $storeUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $urlRewriteModel = $this->_urlRewriteFactory->create();
        /* set current store id */
        $urlRewriteModel->setStoreId(1);
        /* this url is not created by system so set as 0 */
        $urlRewriteModel->setIsSystem(0);
        /* unique identifier - set random unique value to id path */
        $urlRewriteModel->setIdPath(rand(1, 100000));
        /* set actual url path to target path field */
        $urlRewriteModel->setTargetPath("$storeUrl/wuerth/home/index");
        /* set requested path which you want to create */
        $urlRewriteModel->setRequestPath("$storeUrl/home");
        /* save URL rewrite rule */
        $urlRewriteModel->save();
    }

   /**
    * {@inheritdoc}
    */
   public static function getDependencies()
   {
       return [];
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
   public static function getVersion()
   {
      return '2.0.0';
   }
}