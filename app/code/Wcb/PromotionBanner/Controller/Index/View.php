<?php
namespace Wcb\PromotionBanner\Controller\Index;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Wcb\PromotionBanner\Block\PromotionBanner;
 
class View extends Action
{
 
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory, PromotionBanner $promotionBanner)
    {
 
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_promotionBanner = $promotionBanner;
 
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $currentProductId = $this->getRequest()->getParam('currentproduct');
        $banners = $customerLoggedIn = $currentCustomerGroup =  '';

        if ($this->_promotionBanner->getEnable()) {
            $banners = $this->_promotionBanner->getPromotionBanners();
            $customerLoggedIn = $this->_promotionBanner->checkCustomerLoggedIn();
            $currentCustomerGroup = ($customerLoggedIn) ? ($this->_promotionBanner->getCustomerGroup()) : 'Others';
        }

 
        $data = array('currentproductid' => $currentProductId, 'banners' => $banners, 'cl'=> $customerLoggedIn, 'ccg' => $currentCustomerGroup) ;
 
        $block = $resultPage->getLayout()
                ->createBlock('Wcb\PromotionBanner\Block\PromotionBanner')
                ->setTemplate('Wcb_PromotionBanner::view.phtml')
                ->setData('data',$data)
                ->toHtml();
 
        $result->setData(['output' => $block]);
        return $result;
    }
 
}