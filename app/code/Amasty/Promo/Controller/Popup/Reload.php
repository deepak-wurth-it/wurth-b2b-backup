<?php

namespace Amasty\Promo\Controller\Popup;

use Magento\Framework\App\Action\Action;

class Reload extends Action
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Layout $layout,
        \Amasty\Promo\Helper\Data $helper,
        \Amasty\Promo\Model\Config $config
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->helper = $helper;
        $this->config = $config;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        $returnUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);

        if (!$returnUrl) {
            $jsonResult->setHttpResponseCode(403);
            return $jsonResult;
        }

        $products = $this->helper->getPromoItemsDataArray();
        $rawContent = '';
        if ($products['common_qty']) {
            $this->layout->getUpdate()->addHandle('amasty_promo_popup_reload');
            /** @var \Amasty\Promo\Block\Items $popupBlock */
            $popupBlock = $this->layout->getBlock('ampromo.items');
            $popupBlock->setData('current_url', $returnUrl);

            $rawContent = $popupBlock->toHtml();
        }

        $autoOpenPopup = false;
        if ((bool)$this->helper->getNewItems() && $this->config->isAutoOpenPopup()) {
            $autoOpenPopup = true;
        }

        $jsonResult->setData(
            ['popup' => $rawContent, 'products' => $products, 'autoOpenPopup' => $autoOpenPopup],
            true
        );

        return $jsonResult;
    }
}
