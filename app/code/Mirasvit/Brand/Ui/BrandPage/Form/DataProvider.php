<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Ui\BrandPage\Form;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;
use Mirasvit\Brand\Service\ImageUrlService;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var ReadInterface
     */
    private $mediaDirectory;

    private $imageUrlService;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param ImageUploader          $imageUploader
     * @param Filesystem             $filesystem
     * @param Mime                   $mime
     * @param CollectionFactory      $collectionFactory
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUrlService        $imageUrlService
     * @param array                  $meta
     * @param array                  $data
     */
    public function __construct(
        ImageUploader $imageUploader,
        Filesystem $filesystem,
        Mime $mime,
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        ImageUrlService $imageUrlService,
        array $meta = [],
        array $data = []
    ) {
        $this->collection      = $collectionFactory->create()->addStoreColumn();
        $this->dataPersistor   = $dataPersistor;
        $this->imageUrlService = $imageUrlService;
        $this->imageUploader   = $imageUploader;
        $this->mediaDirectory  = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->mime            = $mime;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $storeIds = [];
        if ($data = $this->collection->getData()) {
            foreach ($data as $value) { //prepare store_id for multistore
                $storeIds[$value[BrandPageStoreInterface::BRAND_PAGE_ID]] = $value[BrandPageStoreInterface::STORE_ID];
            }
        }

        $result = [];

        foreach ($this->collection->getItems() as $item) {
            if (isset($storeIds[$item->getId()])) {  //prepare store_id for multistore
                $item->setData(BrandPageStoreInterface::STORE_ID, $storeIds[$item->getId()]);
            }

            $data                   = $item->getData();
            $data                   = $this->prepareImageData($data, 'logo');
            $data                   = $this->prepareImageData($data, 'banner');
            $result[$item->getId()] = $data;
        }

        return $result;
    }

    /**
     * @param array  $data
     * @param string $imageKey
     *
     * @return array
     */
    private function prepareImageData($data, $imageKey)
    {
        if (isset($data[$imageKey])) {
            $imageName = $data[$imageKey];
            unset($data[$imageKey]);
            if ($this->mediaDirectory->isExist($this->getFilePath($imageName))) {
                $data[$imageKey] = [
                    [
                        'name' => $imageName,
                        'url'  => $this->imageUrlService->getImageUrl($imageName),
                        'size' => $this->mediaDirectory->stat($this->getFilePath($imageName))['size'],
                        'type' => $this->getMimeType($imageName),
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getMimeType($fileName)
    {
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($this->getFilePath($fileName));

        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFilePath($fileName)
    {
        return $this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $fileName);
    }
}
