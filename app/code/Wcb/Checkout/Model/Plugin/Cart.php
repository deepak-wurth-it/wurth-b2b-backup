<?php

namespace Wcb\Checkout\Model\Plugin;

class Cart
{
    protected $productRepository;
    protected $storeManager;
    protected $checkoutSession;
    protected $logger;
    protected $type = array('2'=>'100');


    public function __construct( 
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepositoryInterface, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession, 
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection
 
        )
    {
        $this->productRepository = $productrepositoryInterface;
        $this->storeManager      = $storeManager;
        $this->checkoutSession   = $checkoutSession;
        $this->logger            = $loggerInterface;
        $this->connection = $resourceConnection->getConnection();
    }
    public function beforeAddProduct( \Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null )
    {  
        $productId =  $productInfo->getData('entity_id');
        $product = $this->productRepository->getById($productId);     
        $minimum_sales_quantity =  (int)$product->getMinimumSalesUnitQuantity();
        $base_unit_of_measure_id = (int) $product->getBaseUnitOfMeasureId();
        //$base_unit_of_measure_id = '2';
        if($base_unit_of_measure_id){
			$type = $this->getType($base_unit_of_measure_id);
			
			if($type == 'C' || $type='c'){
				$quantity = $this->type[$base_unit_of_measure_id];
				$minimum_sales_quantity = $minimum_sales_quantity * $quantity;
			}
			
		}
        if($minimum_sales_quantity && $requestInfo['qty']){
            $requestInfo['qty'] = $requestInfo['qty'] * $minimum_sales_quantity;
        }
        
          
        return [$productInfo,$requestInfo];
    }
    
    public function getType($base_unit_of_measure_id){
		
		$id = (int) $base_unit_of_measure_id;
	    $selectExist = $this->connection->select()
			->from(
				['uom' => 'unitsofmeasure'],
				['Code']
			)
			->where('unitsofmeasure_id = ?', $id);
			
	    $dataExist = $this->connection->fetchOne($selectExist);	
	    return $dataExist;
	}
}
