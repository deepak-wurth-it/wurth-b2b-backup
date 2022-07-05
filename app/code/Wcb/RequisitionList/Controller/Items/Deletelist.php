<?php

namespace Wcb\RequisitionList\Controller\Items;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\RequisitionList\Model\RequisitionListFactory;

class Deletelist extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var RequisitionListFactory
     */
    protected $requisitionListFactory;

    /**
     * Updateitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param RequisitionListFactory $requisitionListFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        RequisitionListFactory $requisitionListFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->requisitionListFactory = $requisitionListFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $result = [];

        try {
            $listId = $this->getRequest()->getParam('list_id');
            $listData = $this->requisitionListFactory->create()->load($listId);
            if ($listData->getId()) {
                $listData->delete();
                $result['success'] = "true";
                $result['message'] = __("List has been deleted successfully.");
                $this->messageManager->addSuccess($result['message']);
            } else {
                $result['success'] = "false";
                $result['message'] = __("List not found in list.");
                $this->messageManager->addError($result['message']);
            }
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['message'] = __("Something went wrong please try again.");
            $this->messageManager->addError($result['message']);
        }
        $response->setData($result);
        return $response;
    }
}
