<?php

namespace Mirasvit\SearchAutocomplete\Controller\Ajax;

use Exception;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class GetRecentProduct extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * GetRecentProduct constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Image $imageHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        Image $imageHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        $productIds = $this->getRequest()->getParam('productIds');

        $result = [];
        try {
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter("entity_id", ['in', $productIds]);
            $collection->setPageSize(6);
            $data = [];

            foreach ($collection as $product) {
                $data[] = [
                    'id' => $product->getEntityId(),
                    'name' => $product->getName(),
                    'url' => $product->getProductUrl(),
                    'image' => $this->getImageUrl($product)
                ];
            }
            $result['data'] = $data;
            $result['success'] = "true";
            $result['message'] = __("success");
        } catch (Exception $e) {
            $result['data'] = [];
            $result['success'] = "false";
            $result['message'] = __("Something went wrong please try again." . $e->getMessage());
        }
        $response->setData($result);
        return $response;
    }

    /**
     * @param $product
     * @return string
     */
    public function getImageUrl($product)
    {
        return $this->imageHelper->init($product, 'product_page_image_small')
            ->setImageFile($product->getFile())
            ->resize(200, 200)
            ->getUrl();
    }
}
