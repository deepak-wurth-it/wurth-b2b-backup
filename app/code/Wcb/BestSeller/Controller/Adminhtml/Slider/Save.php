<?php

namespace Wcb\BestSeller\Controller\Adminhtml\Slider;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Wcb\BestSeller\Controller\Adminhtml\Slider;
use Wcb\BestSeller\Model\SliderFactory;
use Zend_Filter_Input;

/**
 * Class Save
 * @package Wcb\BestSeller\Controller\Adminhtml\Slider
 */
class Save extends Slider
{
    /**
     * Date filter
     *
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param SliderFactory $sliderFactory
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        Registry $coreRegistry,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor
    ) {
        $this->_dateFilter = $dateFilter;
        $this->dataPersistor = $dataPersistor;

        parent::__construct($context, $sliderFactory, $coreRegistry);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost('slider')) {
            try {
                $data = $this->_filterData($data);
                $slider = $this->_initSlider();

                $validateResult = $slider->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_session->setPageData($data);
                    $this->dataPersistor->set('wcb_bestseller_slider', $data);
                    $this->_redirect('*/*/edit', ['id' => $slider->getId()]);

                    return;
                }

                $slider->addData($data)
                    ->save();
                $this->messageManager->addSuccessMessage(__('The Slider has been saved.'));
                $this->_session->setMageplazaProductsliderSliderData(false);
                $this->dataPersistor->clear('wcb_bestseller_slider');
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $slider->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Slider. %1', $e->getMessage())
                );
                $this->_getSession()->setMageplazaProductsliderSliderData($data);
                $this->dataPersistor->set('wcb_bestseller_slider', $data);
                $sliderId = $this->getRequest()->getParam('id');
                if (empty($sliderId)) {
                    $this->_redirect('*/*/new');
                } else {
                    $this->_redirect('*/*/edit', ['id' => $sliderId->getId()]);
                }

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * filter values
     *
     * @param array $data
     *
     * @return array
     */
    protected function _filterData($data)
    {
        $inputFilter = new Zend_Filter_Input(['from_date' => $this->_dateFilter], [], $data);
        $data = $inputFilter->getUnescaped();

        if (isset($data['responsive_items'])) {
            unset($data['responsive_items']['__empty']);
        }
        $productsWithTitle = $this->getRequest()->getParam('product_id_with_title');
        $productsKeys = json_decode($productsWithTitle, true);
        $productsKeys = array_keys($productsKeys);
        $customProducts = implode('&', $productsKeys);

        if ($products = $this->getRequest()->getParam('products')) {
            $data['product_ids'] = $products;
        }

        if ($productsWithTitle) {
            $data['product_id_with_title'] = $productsWithTitle;
            $data['product_ids'] = $customProducts;
        }

        return $data;
    }
}
