<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wcb\Catalog\Controller\Ajax;

class GetItemEShopSalesPriceAndDisc extends \Magento\Framework\App\Action\Action
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
        return parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $skus = $this->getRequest()->getParam('skus');
        $data = "";
        $xmlData = $this->getSinglePrice($skus);

        if ($xmlData) {
            $data = (array) $xmlData->SoapBody->GetItemEShopSalesPriceAndDisc_Result;
        }

        $result->setData(array('success' => $data));
        return $result;
    }

    public function getSinglePrice($sku)
    {   $sku = '001 512';
        return $this->_soapApiClient->GetItemEShopSalesPriceAndDisc($sku);

    }

    
}
