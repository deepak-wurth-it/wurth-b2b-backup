<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Testimonials\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
    \Wcb\Testimonials\Model\TestimonialsFactory $testimonialFactory
    ) {
        parent::__construct($context);
        $this->testimonialFactory = $testimonialFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * View  page action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $post = $this->testimonialFactory->create();
		$collection = $post->getCollection();
		foreach($collection as $item){
			echo "<pre>";
			print_r($item->getData());
			echo "</pre>";
		}
        $result = $this->resultJsonFactory->create();
        $data = ['message' => 'Hello world!'];

        return $result->setData($data);
    }
    public function getTestimonialsCollection()
    {
        return $this->testimonialFactory->create();
    }
}
