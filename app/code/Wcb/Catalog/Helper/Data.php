<?php

namespace Wcb\Catalog\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Wcb\Catalog\Model\ResourceModel\ProductPdf\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    protected $productLoader;

    protected $connection;

    protected $productRepository;

    protected $registry;

    protected $date;

    protected $priceCurrency;

    protected $checkoutSession;

    protected $type = ['2' => 100];
    /**
     * @var CollectionFactory
     */
    private $_pdfCollectionFactory;
    /**
     * @var UrlInterface
     */
    private $_urlInterface;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    public function __construct(
        ProductRepositoryInterface $productrepositoryInterface,
        ProductFactory $productFactory,
        Registry $registry,
        TimezoneInterface $date,
        Session $checkoutSession,
        CollectionFactory $pdfCollectionFactory,
        UrlInterface $urlInterface,
        StoreManagerInterface $_storeManager,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productrepositoryInterface;
        $this->registry = $registry;
        $this->date = $date;
        $this->checkoutSession = $checkoutSession;
        $this->_pdfCollectionFactory = $pdfCollectionFactory;
        $this->_urlInterface = $urlInterface;

        parent::__construct($context);
        $this->_storeManager = $_storeManager;
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    public function getLoadProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @param $productId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductPdfByProduct($productId)
    {
        $data = array();
        $pdfCollection = $this->_pdfCollectionFactory->create();
        $pdfCollection
            ->addFieldToFilter('product_id', array('eq' => $productId))
            ->addFieldToFilter('pdf_active_status', array('eq' => 1));
        $mainPDf = array();
        $restPDf = array();
        foreach ($pdfCollection as $pdfColl) {
            if ($pdfColl->getEntityId()) {
                if ($pdfColl->getIsMainPdf()) {
                    $mainPDf['type'] = $pdfColl->getPdfTypeId();
                    $mainPDf['pdf_url'] = $this->getProductPdfMediaUrl() . $pdfColl->getPdfUrl();
                    $data['main_pdf'][] = $mainPDf;
                } else {
                    $restPDf['type'] = $pdfColl->getPdfTypeId();
                    $restPDf['pdf_url'] = $this->getProductPdfMediaUrl() . $pdfColl->getPdfUrl();
                    $data['rest_pdf'][] = $restPDf;
                }
            }
        }
        return $data;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductPdfMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). 'product_pdfs/';
    }
}
