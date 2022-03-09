<?php

namespace Wcb\AmastySalesRule\Plugin\Model;

use Amasty\Promo\Model\Rule as AmRule;
use \Magento\SalesRule\Api\RuleRepositoryInterface;
use Amasty\Promo\Model\Registry as ARegistry;
/**
 * Promo Items Registry
 */
class Registry
{

  public function __construct(
      RuleRepositoryInterface $ruleRepositoryInterface,
      AmRule $AmRule
  ) {
      $this->amRule = $AmRule;
      $this->ruleRepositoryInterface = $ruleRepositoryInterface;
  }


  public function beforeAddPromoItem(ARegistry $subject,$sku, $qty, $ruleId, $discountData, $type, $discountAmount)
      {
           $sku_and_quantity = "";
           $rule = $this->ruleRepositoryInterface->getById($ruleId);
           $AmCollection = $this->amRule->getCollection();
           $AmCollection = $AmCollection->addFieldToFilter('salesrule_id',$ruleId);
           if($AmCollection->getSize() != 1){

             return [$sku, $qty, $ruleId, $discountData, $type, $discountAmount];

           }else{
             $sku_and_quantity = $AmCollection->getFirstItem()->getData('discount_products_quantity');
             if(empty($sku_and_quantity)){

               return [$sku, $qty, $ruleId, $discountData, $type, $discountAmount];

             }else{
               $sku_and_quantity = json_decode($sku_and_quantity, true, JSON_UNESCAPED_SLASHES);
               $sku_and_quantity_flipped = array_flip($sku_and_quantity);
               $newQty = array_search($sku,$sku_and_quantity_flipped);
               if($newQty){
                 $qty = $newQty;
               }
             }

           }


          return [$sku, $qty, $ruleId, $discountData, $type, $discountAmount];

      }

}
