<?php

namespace Wcb\BestSeller\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Slider
 * @package Wcb\BestSeller\Controller\Product
 */
class Slider extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Slider constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
