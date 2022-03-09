<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Model;

class SingalProductPrice implements \Wcb\ApiConnect\Api\SingalProductPriceInterface
{

  protected $_pageFactory;
  protected $resultJsonFactory;
  public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $pageFactory,
      \Wcb\ApiConnect\Model\SoapClient $soapApiClient,
      \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
  ) {
      $this->_pageFactory = $pageFactory;
      $this->_soapApiClient = $soapApiClient;
      $this->resultJsonFactory = $resultJsonFactory;
      //return parent::__construct($context);
  }

  public function callSingalProductPrice($sku)
  {
      $data = "";
      $xmlData = $this->getSinglePrice($sku);

      if ($xmlData) {
          $data = (array) $xmlData->SoapBody->GetItemEShopSalesPriceAndDisc_Result;
      }

    return $data;
  }

  public function getSinglePrice($sku)
  {   //$sku = '001 512';
      return $this->_soapApiClient->GetItemEShopSalesPriceAndDisc($sku);

  }
}
