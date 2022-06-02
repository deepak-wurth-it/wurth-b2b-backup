<?php
   
namespace Pim\Product\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\MediaStorage\Model\File\UploaderFactory;
/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportImageService
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * File interface
     *
     * @var File
     */
    protected $file;
    /**
     * ImportImageService constructor
     *
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        DirectoryList $directoryList,
        UploaderFactory $uploaderFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $ProductRepositoryInterface,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->uploaderFactory = $uploaderFactory;
        $this->ProductRepositoryInterface = $ProductRepositoryInterface;
        $this->file = $file;
    }
    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     */
    public function execute($product, $imageUrl, $visible = false, $imageType = [])
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);
        /** @var string $newFileName */
        $newFileName = $tmpDir . baseName($imageUrl);
        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($imageUrl, $newFileName);
        //$imageType = [];
        if ($result) {
			
			
			/*$existingMediaGalleryEntries = $product->getMediaGalleryEntries();
			foreach ($existingMediaGalleryEntries as $key => $entry) {
				
				unset($existingMediaGalleryEntries[$key]);
			}
			$product->setMediaGalleryEntries($existingMediaGalleryEntries);
			$this->ProductRepositoryInterface->save($product);*/
			
            /** add saved file to the $product gallery */
            $product->addImageToMediaGallery($newFileName, $imageType, false, $visible);
        }
        //echo 'fsfdfdsfsffdfdffdfdfdfdfdhghghghgsfds';exit;
        return $result;
    }
    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp'. DIRECTORY_SEPARATOR;
    }
}

