<?php
namespace Wcb\BestSeller\Block\Adminhtml\Slider\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\Tab\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\BlockInterface;
use Wcb\BestSeller\Model\SliderFactory;

class AssignProducts extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Wcb_BestSeller::scriptjs.phtml';

    /**
     * @var Product
     */
    protected $blockGrid;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var Json
     */
    protected $_json;
    /**
     * @var SliderFactory
     */
    private $sliderFactory;

    /**
     * AssignProducts constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param SliderFactory $sliderFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncoderInterface $jsonEncoder,
        SliderFactory $sliderFactory,
        Json $json,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->sliderFactory = $sliderFactory;
        $this->_json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Retrieve instance of grid block
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Products::class,
                'slider.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        return $this->getSelectedProducts();
    }

    /**
     * @return array
     */
    public function getSelectedProducts()
    {
        $slider = $this->getSlider();
        // logic only for existing slider
        if (!$slider->getProductIdWithTitle() && $slider->getProductIds()) {
            $productIds = explode('&', $slider->getProductIds());
            $existsProducts = [];
            foreach ($productIds as $productId) {
                $existsProducts[$productId] = '';
            }
            if (!empty($existsProducts)) {
                return $this->_json->serialize($existsProducts);
            }
        }
        // End logic only for existing slider
        return $slider->getProductIdWithTitle() ?: '{}';
    }

    /**
     * @return Slider
     */
    protected function getSlider()
    {
        $sliderId = $this->getRequest()->getParam('id');
        $slider = $this->sliderFactory->create();
        if ($sliderId) {
            $slider->load($sliderId);
        }

        return $slider;
    }

    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }
}
