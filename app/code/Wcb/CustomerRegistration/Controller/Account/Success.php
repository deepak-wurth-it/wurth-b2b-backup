<?php
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Success extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Success constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UrlInterface $url
    ) {
        $this->_resultPageFactory = $resultPageFactory;

        $this->url = $url;
        $this->response = $context->getResponse();
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     */
    public function execute()
    {
        $email = $this->getRequest()->getParam("email");
        if ($email) {
            return $this->_resultPageFactory->create();
        }
        $noRouteUrl = $this->url->getUrl('noroute');
        $this->getResponse()->setRedirect($noRouteUrl);
    }
}
