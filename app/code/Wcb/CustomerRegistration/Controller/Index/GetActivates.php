<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Wcb\CustomerRegistration\Model\ResourceModel\Division\CollectionFactory as divisionCollection;

class GetActivates extends Action
{
    protected $_resultJsonFactory;

    protected $divisionCollection;

    protected $storeManager;

    public function __construct(
        Context $context,
        divisionCollection $divisionCollection,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory
    ) {
        $this->divisionCollection = $divisionCollection;
        $this->storeManager = $storeManager;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        $result['success'] = "false";
        $result['message'] = "";
        $result['option'] = "";

        $division = $this->getRequest()->getParam("division");
        $optionHtml = "<option value=''>" . __("Please select Activities") . "</option>";
        if ($division) {
            $activatesData =  $this->getActivates($division);
            if ($activatesData->count() > 0) {
                foreach ($activatesData as $activates) {
                    $optionHtml .= "<option value='" . $activates->getName() . "'>";
                    $optionHtml .= $activates->getName();
                    $optionHtml .= "</option>";
                }
                $result['success'] = "true";
                $result['message'] = "Activates find successfully.";
                $result['option'] = $optionHtml;
            } else {
                $result['success'] = "false";
                $result['message'] = "Activates not available for the same division.";
                $result['option'] = $optionHtml;
            }
        } else {
            $result['success'] = "false";
            $result['message'] = __("Please select division.");
            $result['option'] = $optionHtml;
        }

        $response = $this->_resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }

    public function getActivates($division)
    {
        return $this->divisionCollection->create()
            ->addFieldToFilter("parent_branch", ["eq" => $division]);
    }
}
