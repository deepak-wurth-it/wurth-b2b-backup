<?php /**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Store\Controller\Ajax;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Wcb\Store\Model\AddStoreToQuote;

class UpdateStorePickup extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var AddStoreToQuote
     */
    protected $addStoreToQuote;

    /**
     * UpdateStorePickup constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AddStoreToQuote $addStoreToQuote
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddStoreToQuote $addStoreToQuote
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addStoreToQuote = $addStoreToQuote;
        return parent::__construct($context);
    }

    public function execute()
    {
        try {
            $storeData = $this->getRequest()->getParams();
            if ($storeData) {
                $this->addStoreToQuote->setStore($storeData);
            }

            $layout = $this->resultFactory->create(ResultFactory::TYPE_PAGE)
                ->addHandle('checkout_cart_index')
                ->getLayout();
            $itemForm = '';
            if ($layout->getBlock('checkout.cart.form')) {
                $itemForm = $layout->getBlock('checkout.cart.form')->toHtml();
            }

            $result['success'] = "true";
            $result['item_form'] = $itemForm;
            $result['message'] = __("Quote has been updated successfully.");
        } catch (Exception $e) {
            $result['success'] = "false";
            $result['item_form'] = '';
            $result['message'] = $e->getMessage();
        }

        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }

    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }
}
