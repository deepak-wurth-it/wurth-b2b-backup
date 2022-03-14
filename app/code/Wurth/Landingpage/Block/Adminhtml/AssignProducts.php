<?php

namespace Wurth\Landingpage\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;
use Wurth\Landingpage\Model\ResourceModel\LandingPage\CollectionFactory;

class AssignProducts extends Template
{
    /**
     * @var string
     */
    protected $_template = 'products/assign_products.phtml';
    /**
     * @var Registry
     */
    protected $registry;

    protected $blockGrid;
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var CollectionFactory
     */
    protected $productFactory;

    /**
     * AssignProducts constructor.
     * @param Context $context
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param CollectionFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncoderInterface $jsonEncoder,
        CollectionFactory $productFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Wurth\Landingpage\Block\Adminhtml\Tab\Productgrid',
                'category.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return mixed|string
     */
    public function getProductsJson()
    {
        $entity_id = $this->getRequest()->getParam('landing_page_id');
        $productFactory = $this->productFactory->create();
        $productFactory->addFieldToSelect(['product_id']);
        $productFactory->addFieldToFilter('landing_page_id', ['eq' => $entity_id]);
        $result = [];
        if (!empty($productFactory->getData())) {
            foreach ($productFactory->getData() as $landingProducts) {
                return $landingProducts['product_id'];
                //$result[$landingProducts['product_id']] = '';
            }
            return $this->jsonEncoder->encode($result);
        }
        return '{}';
    }

    /**
     * @return mixed|null
     */
    public function getItem()
    {
        return $this->registry->registry('my_item');
    }
}
