<?php

namespace Wcb\MirasvitSearch\Model\Cms\Page;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Wcb\MirasvitSearch\Model\ImageUploader;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    public $collection;
    /**
     * @var $loadedData
     */
    private $loadedData;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
            // echo $model->getCmsImage();exit();
            if ($model->getCmsImage()) {
                $m['cms_image'][0]['name'] = $model->getCmsImage();
                $m['cms_image'][0]['url'] = $this->getMediaUrl() . $model->getCmsImage();
                $fullData = $this->loadedData;
                $this->loadedData[$model->getId()] = array_merge($fullData[$model->getId()], $m);
            }
        }

        $data = $this->dataPersistor->get('cms_page');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('cms_page');
        }

        return $this->loadedData;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . ImageUploader::IMAGE_TMP_PATH . '/';
    }
}
