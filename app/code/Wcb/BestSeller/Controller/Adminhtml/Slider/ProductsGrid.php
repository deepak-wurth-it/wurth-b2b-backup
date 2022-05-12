<?php

namespace Wcb\BestSeller\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class ProductsGrid
 * @package Wcb\BestSeller\Controller\Adminhtml\Slider
 */
class ProductsGrid extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * ProductsGrid constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        Rawfactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;

        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('slider.edit.tab.product')
            ->setInBanner($this->getRequest()->getPost('slider_products', null));

        return $resultLayout;


//        $resultRaw = $this->resultRawFactory->create();
//        return $resultRaw->setContents(
//            $this->layoutFactory->create()->createBlock(
//                'Wcb\BestSeller\Block\Adminhtml\Slider\Edit\Tab\AssignProducts',
//                'slider.edit.tab.product'
//            )->toHtml()
//        );
    }
}
