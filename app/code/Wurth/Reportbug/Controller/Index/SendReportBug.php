<?php

namespace Wurth\Reportbug\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Wurth\Reportbug\Helper\Data as helperData;

class SendReportBug extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var helperData
     */
    protected $helperData;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * SendReportBug constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     * @param helperData $helperData
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        helperData $helperData,
        ManagerInterface $messageManager
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * Send report bug
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->_resultJsonFactory->create();
        $result = [];
        try {
            $isMailSend = $this->sendMail();
            if ($isMailSend) {
                $result['success'] = "true";
                $result['message'] = __("Your report bug has been successfully sent.");
                $this->messageManager->addSuccess($result['message']);
            } else {
                $result['success'] = "false";
                $result['message'] = __("Something went wrong while sending email please try again.");
                $this->messageManager->addError($result['message']);
            }
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['message'] = __("Something went wrong please try again." . $e->getMessage());
            $this->messageManager->addError($result['message']);
        }
        $response->setData($result);
        return $response;
    }

    /**
     * Send email to admin
     *
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail()
    {
        $return = "";
        $email = $this->helperData->getadminEmail();
        $email = explode("|", $email);

        $template = $this->helperData->getEmailTemplate();
        $this->inlineTranslation->suspend();

        $link = $this->getRequest()->getParam("error_link");
        $errorDescription = $this->getRequest()->getParam("error_description");
        $customerName = $this->getRequest()->getParam("customer_name");
        $customerNumber = $this->getRequest()->getParam("customer_number");
        $itemNumber = $this->getRequest()->getParam("item_number");
        $itemName = $this->getRequest()->getParam("item_name");

        $vars = [
            'link' => $link,
            'error_description' => $errorDescription,
            'customer_name' => $customerName,
            'customer_number' => $customerNumber,
            'item_number' => $itemNumber,
            'item_name' => $itemName,
            'store' => $this->getStore()
        ];

        $sender = $this->helperData->getEmailSender();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId()
            ])
            ->setTemplateVars($vars)
            ->setFromByScope($sender)
            ->addTo($email)
            ->getTransport();

        try {
            $transport->sendMessage();
            $return = true;
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
            $this->messageManager->addError($exception->getMessage());
            $return = false;
        }
        $this->inlineTranslation->resume();

        return $return;
    }

    /**
     * Get store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Get store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
