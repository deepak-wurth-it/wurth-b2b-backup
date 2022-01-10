<?php
namespace Wcb\Testimonials\Block;

class Testimonials extends \Magento\Framework\View\Element\Template
{
    protected $testimonialFactory;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
    \Wcb\Testimonials\Model\TestimonialsFactory $testimonialFactory,
    \Magento\Framework\View\Result\PageFactory $pageFactory
    ){
        $this->_pageFactory = $pageFactory;
		$this->testimonialFactory = $testimonialFactory;
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}
    public function getTestimonialsCollection()
    {
        $test = $this->testimonialFactory->create();
		return $test->getCollection();
    }

	/**
	 * @return
	 */
	public function getMediaFolder() {
		$media_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $media_folder;
	}
}