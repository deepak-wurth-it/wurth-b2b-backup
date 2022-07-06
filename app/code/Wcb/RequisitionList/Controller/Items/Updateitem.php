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
use Magento\RequisitionList\Model\RequisitionListItemFactory;

class Updateitem extends Action
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
     * @var RequisitionListItemFactory
     */
    protected $requisitionListItemFactory;

    /**
     * Updateitem constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param RequisitionListItemFactory $requisitionListItemFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        RequisitionListItemFactory $requisitionListItemFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
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
            $itemId = $this->getRequest()->getParam('item_id');
            $qty = $this->getRequest()->getParam('qty');
            $listItemData = $this->requisitionListItemFactory->create()->load($itemId);
            if ($listItemData->getId()) {
                $listItemData->setQty($qty);
                $listItemData->save();
                $result['success'] = "true";
                $result['message'] = __("Item has been updated successfully.");
                $this->messageManager->addSuccess($result['message']);
            } else {
                $result['success'] = "false";
                $result['message'] = __("Item not found in list.");
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
