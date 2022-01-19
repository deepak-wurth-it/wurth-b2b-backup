<?php

namespace Amasty\Conditions\Plugin\Checkout\Model;

use Amasty\Conditions\Model\ResourceModel\Quote as QuoteResourceModel;
use Amasty\Conditions\Model\QuoteFactory;

class TotalInformationManagementPlugin
{
    /**
     * @var QuoteResourceModel
     */
    private $quoteResourceModel;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    public function __construct(
        QuoteResourceModel $quoteResourceModel,
        QuoteFactory $quoteFactory
    ) {
        $this->quoteResourceModel = $quoteResourceModel;
        $this->quoteFactory = $quoteFactory;
    }

    public function afterCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $subject,
        $result,
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        $addressExtAttributes = $addressInformation->getAddress()->getExtensionAttributes();

        if (!$addressExtAttributes || !$addressExtAttributes->getAdvancedConditions()) {
            return $result;
        }

        $payment = $addressExtAttributes->getAdvancedConditions()->getPaymentMethod();

        $quoteModel = $this->quoteFactory->create();
        $this->quoteResourceModel->load($quoteModel, $cartId, 'quote_id');

        $quoteModel->setQuoteId($cartId);
        $quoteModel->setPaymentCode($payment);

        $this->quoteResourceModel->save($quoteModel);

        return $result;
    }
}
